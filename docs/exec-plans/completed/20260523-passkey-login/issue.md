# Execution Plan — Issue

## Title

管理画面のパスキーログイン実装

## Background

ユーザー要望は「パスキーでのログイン処理を実装してほしい。参考は 716 の PR」。

現行 `feature/passkey` は PR #759（`feature/passkey-registration-without-password`）まで取り込まれており、登録フローでは `POST /auth/register/start` と `POST /auth/register/finish`、`PasskeyAuthenticator`、`PasskeyCeremonyStore`、`admin_user_passkeys` が実装済みである。`PasskeyAuthenticator` には認証用の `startAuthentication()` / `finishAuthentication()` も存在し、管理画面側にも `authenticatePasskey()` がある。

一方で、ログイン導線はまだ `POST /auth/login` が `email` / `password` を受け取り、`LoginController` が Laravel guard の `attempt()` を使っている。管理画面の `LoginForm` と BFF `/auth/login` も password 入力・送信を前提としており、登録済み passkey credential を使ってログインできない。

PR #716（`Add admin passkey auth flow`）は過去の調査メモ上では passkey 認証フローの参照元だが、現作業ツリーでは `origin/pull/716/head` 参照は存在しない。必要であれば実装フェーズで GitHub / remote から再取得して参照する。ただし現在の `feature/passkey` には passkey 登録基盤が既に再適用されているため、PR #716 を履歴ごと merge / cherry-pick するのではなく、ログインに必要な設計・差分のみを現行構成へ反映する。

## Goal

登録済み passkey credential を持つ管理ユーザーが、管理画面のログインフォームから WebAuthn 認証を完了し、既存と同じ access token / refresh token セッションでログインできるようにする。パスワードを使う既存ログイン前提を passkey ログイン前提へ置き換える。

## Scope

- コントラクト
  - `src/contracts/src/admin/auth/domain.tsp`
  - `src/contracts/src/admin/auth/service.tsp`
  - `src/contracts/src/admin/auth/transport.tsp`
  - passkey ログイン開始・完了 API の request / response を追加または既存 `login` 契約を置換する。
- サーバ HTTP / Route / Presenter
  - `src/server/routes/admin.php`
  - `src/server/packages/Auth/Route/AuthRouteMap.php`
  - `src/server/app/Http/Controllers/Api/Admin/V1/Auth/LoginController.php`
  - `src/server/app/Http/Presenters/Api/Admin/V1/Auth/LoginPresenter.php`
  - 必要に応じて passkey ログイン start / finish 用 Controller / Presenter を追加する。
- サーバ Application / Domain / Infrastructure
  - `src/server/packages/Auth/Application/Admin/UseCase/Login/`
  - `src/server/packages/Auth/Domain/Models/AdminUserPasskeyRepositoryInterface.php`
  - `src/server/packages/Auth/Infrastructures/AdminUserPasskeyRepository.php`
  - `src/server/packages/Auth/Domain/Services/PasskeyAuthenticatorInterface.php`
  - `src/server/packages/Auth/Infrastructures/PasskeyAuthenticator.php`
  - `src/server/packages/Auth/Domain/Models/PasskeyCeremonyState.php`
  - `src/server/packages/Auth/Domain/Models/PasskeyCeremonyStoreInterface.php`
  - email から対象管理ユーザーと passkey credential を特定し、WebAuthn assertion を検証し、sign count / last used を更新して既存 `LoginUseCase` 相当の token 発行・監査ログ記録を行う。
- 管理画面 UI / BFF
  - `src/admin/src/components/auth/LoginForm.tsx`
  - `src/admin/src/server/routes/auth.ts`
  - `src/admin/src/utils/webauthn.ts`
  - ログインフォームから password 入力を廃止し、email 送信後に `authenticatePasskey()` を実行して完了 API へ credential を送る。
- 生成物とテスト
  - `src/contracts/generated/oas/IsekaiObservatory.Admin.v1.yaml`
  - `src/admin/src/generated/`
  - `src/server/Generated/`
  - `src/server/tests/Feature/Api/Admin/V1/Auth/`
  - `src/server/tests/Unit/Auth/Application/Admin/UseCase/Login/` または実装配置に対応する unit test
  - `src/admin/src/server/routes/auth.test.ts`

## Non-Scope

- passkey 登録フロー（`/auth/register/start`, `/auth/register/finish`）の仕様変更
- 既存管理ユーザーへ passkey を追加登録・再登録する管理 UI
- パスワードログインとの dual auth 維持や移行期間の互換性対応
- `admin:invite`、登録トークン、招待メール送信の仕様変更
- refresh token 自動更新、CSRF、Redis セッション保存方式の再設計
- Viewer 側（`src/viewer/`）の変更
- PR #716 のコミット履歴をそのまま取り込むこと

## Acceptance Criteria

- 管理画面のログインフォームにパスワード入力欄が表示されず、email とブラウザの passkey 認証操作でログインを開始・完了できる。
- 登録済み passkey を持つ管理ユーザーの email でログイン開始 API を呼ぶと、WebAuthn authentication 用の `authCeremonyId` と `publicKey` options が返る。
- ログイン開始で返った `publicKey` options を使ってブラウザが生成した assertion credential をログイン完了 API に送ると、API が `accessToken`、`refreshTokenId`、`refreshToken` を返し、管理画面 BFF が既存と同じ session / csrf cookie を設定する。
- ログイン成功時、使用された `admin_user_passkeys` の `sign_count` と `last_used_at` が更新され、ログインの監査ログが記録される。
- 未登録 email、passkey 未登録ユーザー、存在しない/期限切れ/種別不一致の ceremony、credential id 不一致、WebAuthn 検証失敗では token が発行されず、BFF/UI で認証失敗として扱えるエラーになる。
- WebAuthn 検証失敗時は `admin_user_passkeys` の `sign_count` / `last_used_at` が更新されず、refresh token も保存されない。
- contract / generated client / generated PHP SDK / BFF のログイン request から password 前提がなくなり、`LoginForm` も password を送信しない。
