# Title

リフレッシュトークンによるログインセッション自動更新

## Status

completed

## Background

管理画面の BFF は `src/admin/src/server/routes/auth.ts` のログイン時に `accessToken` と `refreshToken` を Redis セッションへ保存しているが、その後の API 呼び出しでは `src/admin/src/server/client.ts` 経由で `accessToken` しか使っていない。`src/admin/src/server/middleware.ts` は Redis セッションの存在と CSRF だけを検証するため、API 側の `accessToken` が失効すると、Redis セッションが残っていても各画面・BFF ルートは 401 になり再ログインが必要になる。サーバー側には `src/server/packages/Auth` 配下に refresh token の発行・保存基盤がある一方、現行契約 `src/contracts/src/admin/auth/service.tsp` とルート `src/server/packages/Auth/Route/AuthRouteMap.php` には `login` しかなく、トークン再発行 API が未提供である

## Goal

管理画面のログインセッションが有効な refresh token を持つ間は、失効した `accessToken` を自動更新し、ユーザーを `/auth/login` へ戻さず継続利用できる状態にする

## Scope

- `src/contracts/src/admin/auth/service.tsp` と `src/contracts/src/admin/auth/transport.tsp` に、refresh token を用いたトークン再発行契約を追加する
- `src/server/packages/Auth/Application`、`src/server/app/Http/Controllers/Api/Auth`、`src/server/app/Http/Presenters/Api/Auth`、`src/server/routes/admin.php` に、自動更新で使う認証 API の use case / controller / presenter / route を追加または拡張する
- `src/server/packages/Auth/Domain/Models/Token/RefreshToken` と `src/server/packages/Auth/Infrastructures/Token/RefreshToken` で、refresh token の検証と必要な状態更新を扱えるようにする
- `src/admin/src/server/routes/auth.ts`、`src/admin/src/server/middleware.ts`、`src/admin/src/server/client.ts`、必要ならその周辺に、401 発生時に refresh token でセッション資格情報を更新する処理を追加する
- `src/server/tests/Feature/Api/Auth`、`src/server/tests/Unit/Auth` または `Integration/Auth`、および admin 側の関連テスト/検証コードに、自動更新の正常系と失敗系を追加する

## Non-Scope

- ログインフォーム UI (`src/admin/src/components/auth/LoginForm.tsx`) の見た目変更
- 管理画面の権限モデルや `authGuard` の CSRF 方針変更
- refresh token の有効期限日数や Redis セッション TTL の再設計
- viewer 側 (`src/viewer`) の認証導入や共通化

## Acceptance Criteria

- `src/contracts/src/admin/auth` に refresh token で新しい資格情報を取得する API 契約が存在する
- サーバー API は、有効な refresh token を受け取ると新しい `accessToken` を返し、無効・期限切れ・不正な token では認証エラーを返す
- admin BFF は、API 呼び出し時に `accessToken` 失効を検知した場合、保存済み refresh token で 1 回だけ再発行を試み、成功時は Redis セッション内の資格情報を更新して元の処理を継続する
- refresh token での再発行に失敗した場合のみ、既存どおり未認証として扱われ、画面遷移先は `/auth/login` になる
- 自動更新の正常系と失敗系をカバーするサーバー側テスト、または同等の検証手段が追加されている

## Steps

1. ✅ `src/contracts/src/admin/auth/service.tsp` と `src/contracts/src/admin/auth/transport.tsp` に refresh endpoint の request/response を追加し、`mise run generate:server` と `mise run generate:client:admin` の前提になる契約差分を定義する。`login` と同じ namespace / error shape を保ち、admin BFF が refresh token 文字列だけで再発行できる形に揃える
2. ✅ 生成物に合わせて `src/server/packages/Auth/Route/AuthRouteMap.php` と `src/server/routes/admin.php` の auth ルートを拡張し、refresh 用 controller / presenter / use case を追加する配置を確定する。既存の `LoginController` / `LoginPresenter` と同じ責務分離を維持する
3. ✅ `src/server/packages/Auth/Application` に refresh 用 use case を追加し、平文 refresh token の検証、有効 token の取得、必要なら新しい access token 発行までを 1 つのユースケースに閉じ込める。既存 `AuthenticateUseCase` が access token の `jti` から refresh token を参照しているため、その流れと整合するように token 文字列の照合ロジックを組み立てる
4. ✅ `src/server/packages/Auth/Domain/Models/Token/RefreshToken` と `src/server/packages/Auth/Infrastructures/Token/RefreshToken` を最小拡張し、refresh token の平文照合と失効判定を use case から安全に使えるようにする。repository interface には「有効 token の検索」に必要な API だけを追加し、token の消費やローテーションが不要なら今回のスコープでは持ち込まない
5. ✅ `src/server/app/Http/Controllers/Api/Auth` と `src/server/app/Http/Presenters/Api/Auth` に refresh endpoint を実装し、正常時は新しい `accessToken` を返し、無効・期限切れ・不正 token は 401 を返すようにする。OpenAPI validator に通るレスポンス shape を login と同様に presenter 側で確定させる
6. ✅ 契約更新後の生成コードに追従して `src/admin/src/server/client.ts` を拡張し、`accessToken` 付き client の生成と、refresh 呼び出しを内包した「認証付き呼び出し」ヘルパを用意する。各 route で個別に再試行を書かずに済むよう、401 時だけ 1 回 refresh して再実行する責務をここへ寄せる
7. ✅ `src/admin/src/server/routes/auth.ts`、`src/admin/src/server/middleware.ts`、必要なら新規 helper に、Redis session の資格情報更新処理を追加する。refresh 成功時は保存済み `accessToken` を差し替え、refresh 不成功時は既存どおり未認証として扱えるように session 取得・保存の境界を整理する
8. ✅ `src/admin/src/server/routes/*` の API 呼び出しを新しい認証ヘルパへ切り替え、既存の `createAuthClient(credential)` 直呼びを減らす。複数 API を叩く route でも同一 request 内で更新後 credential を使い回せることを確認し、不要な重複 refresh を避ける
9. ✅ `src/server/tests/Feature/Api/Auth` と `src/server/tests/Unit/Auth` または `src/server/tests/Integration/Auth` に、refresh endpoint の正常系・無効 token・期限切れ token を追加する。必要に応じて admin 側にも session 更新 helper の検証コードを追加し、最後に関連テストと生成コマンドを実行して acceptance criteria を確認する

## Decision Log

- 2026-05-03: refresh token 再発行は既存 login API の拡張ではなく独立 endpoint として追加する。admin BFF は `accessToken` と `refreshToken` を別々に保持しており、失効検知後だけ呼ぶ責務を分離したほうが契約と失敗時の扱いを明確にできるため
- 2026-05-03: admin 側の自動更新ロジックは各 route へ散らさず `src/admin/src/server/client.ts` 周辺の共通ヘルパに寄せる。`createAuthClient(credential)` の利用箇所が多く、個別対応では漏れと重複が出やすいため
- 2026-05-03: 今回は refresh token ローテーションを前提にしない。Acceptance Criteria は新しい `accessToken` の自動更新までを求めており、既存サーバー実装も `RefreshTokenRepository::findActive` と `AccessTokenIssueService` の再利用で要件を満たせる見込みのため、変更影響を最小化する
- 2026-05-03: refresh token の平文文字列は `<refresh_token_id>.<secret>` 形式で返す。lookup を `refresh_token_id` で 1 件に絞ってから hash verify できるようにし、DB 全件照合を避けつつ既存の hashed token 保存方式を維持するため

## Validation

- `src/contracts/src/admin/auth` 更新後に生成コマンドを実行し、server/client の生成が成功することを確認する
- `src/server/tests/Feature/Api/Auth` で refresh endpoint の 200 / 401 系を検証し、有効 token で `accessToken` が返ること、無効・期限切れ token で認証失敗になることを確認する
- `src/server/tests/Unit/Auth` または `Integration/Auth` で refresh token 照合ロジックと repository 拡張の境界を検証する
- admin 側は関連テスト、または BFF helper の検証で、401 発生時に 1 回だけ refresh して Redis session の `accessToken` が更新されること、refresh 失敗時は未認証扱いへフォールバックすることを確認する
