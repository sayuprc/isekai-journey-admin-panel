# Execution Plan — Issue

## Title

管理ユーザー登録トークンを使った WebUI 登録導線の追加

## Background

直近マージされた `admin:invite` CLI(`src/server/app/Console/Commands/AdminUser/InviteCommand.php` / `AdminUser\Application\Cli\UseCase\IssueRegistrationToken`)により、登録対象者の email・role・permissions を含む登録トークンを発行し、`admin_user_registration_tokens` テーブルへハッシュ保存できるようになった。平文トークンは発行時に標準出力に 1 度だけ表示する仕様

しかし WebUI(`src/admin/`)側にはまだこのトークンを消費する導線が無く、現状トークンを発行しても `admin:create` を経由しないと管理ユーザーを作成できない。本タスクでは、招待された対象者がブラウザでトークンとパスワードを入力するだけで自分の管理ユーザーを作成し、ログイン状態に遷移できるようにする

## Goal

`admin:invite` で発行された平文トークンを使い、WebUI 上で対象者がパスワードと名前を入力すると、対応する `admin_user_registration_tokens` 行が消費済みになると同時に `admin_users`(および role/permissions)が作成され、そのままログイン状態でダッシュボードに遷移できるようにする

## Scope

- コントラクト
  - `src/contracts/src/admin/auth/` 配下(`domain.tsp` / `transport.tsp` / `service.tsp`)に登録 API(`register` / `POST /auth/register`)を追加。リクエストは平文トークン・email・名前・パスワード、レスポンスはログイン時と同等の `accessToken` / `refreshTokenId` / `refreshToken`
- サーバ HTTP 層
  - `src/server/app/Http/Controllers/Api/Admin/V1/Auth/RegisterController.php` を追加
  - `src/server/app/Http/Presenters/Api/Admin/V1/Auth/RegisterPresenter.php` を追加(400 は固定文言、422 はバリデーション詳細)
  - `src/server/packages/Auth/Route/AuthRouteMap.php` / `src/server/routes/admin.php` にルートを追加
- サーバ Application / Domain 層
  - `src/server/packages/AdminUser/Application/Admin/UseCase/Register/` に `RegisterInputData` / `RegisterOutputData` / `RegisterUseCase` を追加。Refresh/Access Token の発行と永続化まで同一トランザクション内で行う
  - `AdminUser\Domain\Models\RegistrationToken\RegistrationTokenRepositoryInterface` に `findByEmailForUpdate(Email)`(行ロック取得)と `save(RegistrationToken)`(upsert)を追加し、対応する `Infrastructures/RegistrationToken/RegistrationTokenRepository` を拡張
  - `RegistrationToken` に `consume()` と `isAvailable(DateTimeImmutable)` を追加(期限と消費状態をまとめて判定)
  - `AdminUser\Domain\Services\RegistrationToken\RegistrationTokenConsumeService` を追加し、トークン検証(ハッシュ照合・有効期限・未消費判定)を集約。既存 `TokenHasherInterface` / `ConsumptionStatus` / `ExpiredAt` を利用
  - 登録成功と同時にログイン用のアクセストークン/リフレッシュトークンを `Auth\Domain\Services\Token\RefreshToken\RefreshTokenIssueService` / `AccessTokenIssueService` を直接利用して発行・永続化(`LoginUseCase` には依存しない)
- 管理画面フロント / BFF
  - `src/admin/src/pages/auth/register.astro` を追加
  - `src/admin/src/components/auth/RegisterForm.tsx` を追加(`token` / `email` / `name` / `password` の 4 入力)
  - `src/admin/src/server/routes/auth.ts` に `POST /auth/register` を追加し、成功時にセッション Cookie / CSRF Cookie を発行
  - `src/admin/src/server/routes/auth.test.ts` に BFF テストを追加
- 生成物の更新
  - `mise run admin:generate` で OpenAPI クライアントと PHP SDK を更新する

## Non-Scope

- `admin:invite` CLI 自体(`InviteCommand` / `IssueRegistrationTokenUseCase` / ドメインモデル / DB スキーマ)の挙動変更
- `admin_user_registration_tokens` のテーブル定義変更(既存スキーマで足りる前提)
- パスキー登録ロジックの実装(パスワード登録のみを対象とする)
- 招待のメール送信・通知などの配布手段
- トークン失効・再発行・一覧表示 CLI / API
- Viewer 側 (`src/viewer/`) の変更
- 既存ログイン / リフレッシュ API・既存 `admin:create` CLI の挙動変更

## Acceptance Criteria

- 平文トークン・email・パスワード・名前を入力し送信すると、API が 200 を返し、`admin_users` に該当 email のユーザーが 1 行追加され、`admin_user_registration_tokens` の該当行が消費済み(`status = Consumed`)に更新される
- 同時に `admin_user_roles` / `admin_user_permissions` 相当に CLI で指定された role / permissions が反映される(CLI が保存した値がそのまま `AdminUser` に移ること)
- レスポンスを受けた管理画面 BFF がセッション Cookie / CSRF Cookie を発行し、登録完了後にユーザーがログイン状態のまま `/song-types` 等の認証必須ページへ遷移できる
- 以下のケースでは管理ユーザーが作成されず、`admin_user_registration_tokens` も状態変化せず、UI 上に検証可能なエラーメッセージが表示される
  - 入力 email に該当する `admin_user_registration_tokens` 行が存在しない、または平文トークンが該当行のハッシュと一致しない
  - 該当トークン行の `expired_at` を過ぎている
  - 該当トークン行の `status` が既に `Consumed`
  - 入力 email / パスワード / 名前のバリデーション違反
  - 該当トークンの email と既存 `admin_users` の email が衝突する
- 既存の `admin:invite` / `admin:create` / ログイン / リフレッシュ API のテストが回帰せずグリーンのまま
- `mise run api:ecs` / `api:phpstan` / `api:arkitect` / `api:test`、`mise run contract:test`、`cd src && bun --filter admin lint:check` / `style:check` / `build` がすべて成功する
