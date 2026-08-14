# Execution Plan — Plan

実装計画。問題定義は同ディレクトリの `issue.md` を参照する

## Title

管理ユーザー登録時のパスキー同時登録とパスワード廃止

## Status

completed

## Steps

1. ✅ **コントラクトを passkey 登録フローへ変更する** (`src/contracts/src/admin/auth/`)
   - `domain.tsp`: `authCeremonyId`、`webAuthnPublicKeyOptions = Record<unknown>`、`webAuthnCredential = Record<unknown>` を追加する。`password` scalar は既存ログイン用に残す
   - `transport.tsp`: password を含む既存 `RegisterRequest` / `RegisterResponse` を削除または置換し、#716 の命名に寄せた `RegisterStartRequest { token, email, name }`、`RegisterStartResponse { authCeremonyId, publicKey }`、`RegisterFinishRequest { authCeremonyId, credential }`、`RegisterFinishResponse` を追加する。`RegisterFinishResponse` は現行登録成功時の session/access token 返却コントラクトに合わせる
   - `service.tsp`: `AuthenticateService` で既存 `@route("/register") register(...)` を残さず、`@route("/register/start") registerStart(...)` と `@route("/register/finish") registerFinish(...)` に置換する
   - 契約変更後に `mise run contract:format:check` と `mise run contract:test` を実行する

2. ✅ **生成物と WebAuthn 依存を更新する**
   - `mise run contract:compile:admin`、`mise run api:generate`、`mise run admin:generate` を実行し、`src/contracts/generated/oas/IsekaiObservatory.Admin.v1.yaml`、`src/server/Generated/`、`src/admin/src/generated/` を再生成する。生成物は手動編集しない
   - `src/server/composer.json` / `composer.lock` に #716 と同じ `web-auth/webauthn-lib` 依存を追加する
   - `src/server/config/auth.php` に #716 の passkey 設定 (`rp_name` / `rp_id` / `origin` / `timeout_ms` / `ceremony_ttl_seconds` / `ceremony_cache_store`) を現行 config へ再適用する

3. ✅ **passkey の Domain / Infrastructure / DB 基盤を再適用する**
   - #716 を参照し、`src/server/packages/Auth/Domain/Models/{AdminUserPasskey,PasskeyCeremonyState,PasskeyCeremonyStoreInterface}.php`、`src/server/packages/Auth/Domain/Models/AdminUserPasskeyRepositoryInterface.php`、`src/server/packages/Auth/Domain/Services/{PasskeyAuthenticatorInterface,PasskeyStartResult,PasskeyVerificationResult}.php` を追加する
   - #716 を参照し、`src/server/packages/Auth/Infrastructures/{AdminUserPasskeyRepository,PasskeyAuthenticator,PasskeyCeremonyStore,PasskeyCredentialRecordConverter}.php` を追加する。namespace は現行の `Auth\Application\Admin` 配置に合わせ、ログイン系 UseCase はこのタスクでは追加しない
   - `src/server/app/Models/AdminUser/AdminUserPasskey.php` を追加し、`admin_user_passkeys` 用 Eloquent model を定義する
   - `src/server/app/Providers/Domain/AuthServiceProvider.php` に `AdminUserPasskeyRepositoryInterface`、`PasskeyAuthenticatorInterface`、`PasskeyCeremonyStoreInterface` の binding を追加する
   - `src/server/database/atlas/schemas/admin-user-passkeys.my.hcl` を #716 から再適用し、`admin_users` への外部キー、`credential_id` unique、`sign_count`、`last_used_at` を定義する

4. ✅ **password 非依存の AdminUser 永続化へ寄せる**
   - `src/server/database/atlas/schemas/admin-users.my.hcl`: `admin_users.password` カラムを削除する。`dump.sql` は触らない
   - `src/server/app/Models/AdminUser/AdminUser.php`: `password` の PHPDoc を削除する
   - `src/server/packages/AdminUser/Domain/Models/AdminUserRepositoryInterface.php`: `register(AdminUser $adminUser): AdminUser` に変更する
   - `src/server/packages/AdminUser/Infrastructures/AdminUserRepository.php`: `admin_users.password` へ値を保存しない
   - `src/server/packages/AdminUser/Domain/Models/HashedPassword.php` と password 用 `HasherInterface` / `Hasher` を削除する
   - `src/server/tests/Integration/AdminUser/Infrastructures/AdminUserRepositoryTest.php` に password カラムなしで登録できるケースを追加する

5. ✅ **登録 start UseCase / HTTP を追加する**
   - `src/server/packages/AdminUser/Application/Admin/UseCase/RegisterStart/{RegisterStartInputData,RegisterStartOutputData,RegisterStartUseCase}.php` を追加する
   - `RegisterStartUseCase` は `Email::create`、`RegistrationTokenConsumeService::verify`、`AdminUserIntegrityService::prepareForCreate` で token/email/name/email 衝突を事前検証し、`UuidGeneratorInterface` で `adminUserId` と `authCeremonyId` を生成する
   - 同 UseCase は `PasskeyAuthenticatorInterface::startRegistration($adminUserId, $email, $name)` で WebAuthn publicKey options を作り、`PasskeyCeremonyStoreInterface::put()` に `type = register`、token、email、name、adminUserId、optionsJson を保存する。DB のユーザー作成・トークン消費はまだ行わない
   - `src/server/app/Http/Controllers/Api/Admin/V1/Auth/RegisterStartController.php` と `src/server/app/Http/Presenters/Api/Admin/V1/Auth/RegisterStartPresenter.php` を追加し、`RegisterStartResponse` を返す。token 不一致/期限切れ/消費済み/email 衝突は列挙対策で 400 の汎用エラーに丸める
   - `src/server/packages/Auth/Route/AuthRouteMap.php` に `RegisterStart` を追加し、`src/server/routes/admin.php` の auth group に `POST /register/start` を追加する

6. ✅ **登録 finish UseCase / HTTP を passkey credential 検証 + 原子保存として追加する**
   - `src/server/packages/AdminUser/Application/Admin/UseCase/RegisterFinish/{RegisterFinishInputData,RegisterFinishOutputData,RegisterFinishUseCase}.php` を追加し、既存 password 登録用 `Register` UseCase は削除または置換対象にする
   - `RegisterFinishInputData` は `authCeremonyId` と `credential` (`array<string,mixed>`) を受け取る。token/email/name/adminUserId は request から受け取らず、ceremony state から復元する
   - `src/server/app/Http/Controllers/Api/Admin/V1/Auth/RegisterFinishController.php` は request から `authCeremonyId` と `credential` を読み、`RegisterFinishInputData` へ渡す。既存 `RegisterController` は password 登録 API として残さない
   - `RegisterFinishUseCase` は ceremony state を取得し、`type = register`、token、email、name、adminUserId、optionsJson を復元した後、`finishRegistration($credential, $state->optionsJson)` を実行する。検証失敗時は `AuthenticationError` または presenter で扱える汎用 error に変換し、DB 変更を行わない
   - passkey 検証成功後、`TransactionInterface::scope` 内で `RegistrationTokenConsumeService::verify` により token/email/name を再検証し、`AdminUserIntegrityService::prepareForCreateWithId`(未存在なら `AdminUserIntegrityService` に追加)、`AdminUserRepositoryInterface::register($adminUser)`、`AdminUserPasskeyRepositoryInterface::save(...)`、`RegistrationTokenRepositoryInterface::save($token->consume())`、Refresh/Access Token 発行・保存を順に実行する
   - `src/server/app/Http/Presenters/Api/Admin/V1/Auth/RegisterFinishPresenter.php` を追加し、`InvalidInputError` を 422、それ以外を 400 汎用メッセージに丸める方針を維持する
   - `src/server/packages/Auth/Route/AuthRouteMap.php` に `RegisterFinish` を追加し、`src/server/routes/admin.php` の auth group に `POST /register/finish` を追加する。既存 `POST /register` ルートは削除する

7. ✅ **管理画面 BFF / UI を passkey 登録へ変更する**
   - `src/admin/src/utils/webauthn.ts` を #716 から再適用し、`registerPasskey(publicKey)` を提供する。ログイン用 `authenticatePasskey` は後続ログイン導線で使うため入れてもよいが、このタスクでは呼び出さない
   - `src/admin/src/server/routes/auth.ts`: `authenticateServiceRegisterStart` を使う `.post('/register/start', ...)` と `authenticateServiceRegisterFinish` を使う `.post('/register/finish', ...)` を追加する。既存 `.post('/register', ...)` は password 登録 API として残さない。finish 成功時だけ既存どおり session/CSRF Cookie を発行する
   - `src/admin/src/components/auth/RegisterForm.tsx`: password 欄を削除し、token/email/name 入力にする。submit 時は BFF `/auth/register/start` → `registerPasskey(publicKey)` → BFF `/auth/register/finish` の順に呼び、成功時は既存と同じ遷移を行う
   - `src/admin/src/pages/auth/register.astro` はレイアウトを維持し、必要ならフォーム幅だけ調整する
   - `src/admin/src/server/routes/auth.test.ts`: start 呼び出しと finish 呼び出しの body に password が含まれないこと、finish 成功時 cookie/Redis 保存、API エラー時に cookie が出ないことを検証する

8. ✅ **テストを passkey 前提へ更新・追加する**
   - `src/server/tests/Unit/AdminUser/Application/Admin/UseCase/RegisterFinish/RegisterFinishUseCaseTest.php`: password hash 期待を削除し、ceremony state 取得、token/email/name/adminUserId の復元と再検証、passkey 検証、`AdminUserPasskeyRepository::save`、トークン消費、Refresh/Access Token 発行の順を検証する。passkey 検証失敗では repository save/token consume が呼ばれないことも確認する
   - `src/server/tests/Unit/AdminUser/Application/Admin/UseCase/RegisterStart/RegisterStartUseCaseTest.php` を追加し、有効 token で publicKey/authCeremonyId を返して state 保存すること、未知 token/email 不一致/期限切れ/消費済み/email 衝突/入力不正で state 保存しないことを検証する
   - `src/server/tests/Feature/Api/Admin/V1/Auth/RegisterStartTest.php` を追加し、`POST /auth/register/start` の成功・主要失敗系を確認する
   - `src/server/tests/Feature/Api/Admin/V1/Auth/RegisterFinishTest.php`: request から password/token/email/name を削除し、WebAuthn 検証は `PasskeyAuthenticatorInterface` を fake/mock binding して成功 credential と失敗 credential を検証する。成功時は `admin_users` に password 属性がないこと、`admin_user_registration_tokens.status = Consumed`、`admin_user_passkeys` 1 件、refresh token 1 件を assert する
   - `src/server/tests/Integration/Auth/Infrastructures/AdminUserPasskeyRepositoryTest.php` を追加し、保存・credentialId 検索・adminUserId 検索・counter 更新を確認する
   - 既存 `InviteCommandTest`、`IssueRegistrationTokenUseCaseTest`、`RegistrationTokenRepositoryTest` は password 廃止以外の挙動が変わらないことを回帰対象にする

9. ✅ **検証と差分確認を行う**
   - `mise run migrate:dry-run` と `mise run migrate:testing` で `admin_user_passkeys` 追加と `admin_users.password` 削除を確認する。`dump.sql` は変更しない
   - `mise run api:ecs`、`mise run api:phpstan`、`mise run api:arkitect`、`mise run api:test` を実行する。必要に応じて passkey 追加範囲に絞った path 指定から始める
   - `mise run contract:format:check`、`mise run contract:test`、`mise run admin:lint`、`mise run admin:style`、`bunx tsc --noEmit -p src/admin/tsconfig.json` を実行する
   - `git diff -- dump.sql` が空であること、`RegisterStartRequest` / `RegisterFinishRequest` / generated client / generated PHP SDK に登録用 `password` が残っていないこと、既存 `POST /auth/register` が残っていないことを確認する

## Decision Log

- 2026-05-23: PR #716 は `origin/pull/716/head` を参照元として使い、merge / cherry-pick では取り込まない。現行 `feature/passkey` は #756 の登録トークン導線を持っているため、token/email/name の検証と消費は現行 `RegistrationTokenConsumeService` と登録成功時のトランザクション責務を基準に再適用する
- 2026-05-23: `RegisterOptions` 命名は採用せず、PR #716 の passkey auth flow と同じ start/finish に合わせる。登録フローは `POST /auth/register/start` で ceremony を開始し、`POST /auth/register/finish` で credential 検証と永続化を完了する。既存 `POST /auth/register` は password 登録 API として残さない
- 2026-05-23: start 時にも token/email/name/email 衝突を検証するが、DB 更新は行わない。finish 時は ceremony state から token/email/name/adminUserId を復元して再検証し、passkey 検証後のトランザクション内でユーザー作成・passkey 保存・トークン消費・refresh token 保存をまとめることで、途中失敗時の部分保存を避ける
- 2026-05-23: `admin_users.password` は nullable ではなくカラム自体を削除する。ランダムパスワードを生成してハッシュ保存すると「password 廃止」が曖昧になるため、新規 passkey 登録ユーザーも CLI 作成ユーザーも password を保存しない方針にする
- 2026-05-23: passkey ログイン導線は Non-Scope のため、#716 の login start/finish controller/use case/UI はこの計画に含めない。ただし passkey credential model/repository/authenticator は後続ログイン実装でも使える形で Auth package に置く
- 2026-05-23: token 不一致、未知 email、期限切れ、消費済み、email 衝突、passkey 検証失敗は外部に詳細を出さず 400 汎用エラーへ丸める。入力形式の違反だけ 422 として field error を返す
- 2026-05-23: `bunx tsc --noEmit -p src/admin/tsconfig.json` で生成型に対する既存の song/release BFF 型不整合が表面化したため、`Media.publishedAt` の補完と release enum body schema の型を最小修正した。passkey 実装外の挙動変更は行わない

## Validation

- **AC1 登録フォーム**: `RegisterForm.tsx` の DOM に password input がなく、token/email/name 入力と passkey ceremony 呼び出しだけで登録送信することをコードレビューで確認する
- **AC2 成功時の DB 状態**: `RegisterFinishTest` 成功ケースで `admin_users` 作成、`admin_user_registration_tokens.status = Consumed`、`admin_user_passkeys` 1 件、refresh token 1 件を assert する
- **AC3 password 廃止**: 同 Feature/Integration テストで passkey 登録ユーザーの `admin_users` に password 属性がないことを assert し、request/UI/BFF から password が消えていることを `auth.test.ts` と generated 型確認で検証する
- **AC4 contract/generated**: `RegisterStartRequest` / `RegisterFinishRequest` の TypeSpec、`src/contracts/generated/oas/IsekaiObservatory.Admin.v1.yaml`、`src/admin/src/generated/`、`src/server/Generated/` に登録用 `password` が存在せず、`POST /auth/register/start` と `POST /auth/register/finish` が生成され、既存 `POST /auth/register` が残っていないことを `rg` と生成後差分で確認する
- **AC5 passkey 検証失敗の原子性**: `RegisterFinishUseCaseTest` と `RegisterFinishTest` で passkey 検証失敗時に `admin_users`、`admin_user_registration_tokens.status`、`admin_user_passkeys`、refresh token が変わらないことを確認する
- **AC6 token/input 失敗の原子性**: `RegisterStartUseCaseTest` / `RegisterFinishUseCaseTest` / `RegisterStartTest` / `RegisterFinishTest` で未知 token、email 不一致、期限切れ、消費済み、email 衝突、名前などの入力違反に対して DB 変更がないことを確認する
- **AC7 invite 回帰なし**: `InviteCommandTest`、`IssueRegistrationTokenUseCaseTest`、`RegistrationTokenRepositoryTest` を含めて `mise run api:test` を実行し、`admin:invite` と登録トークン検証が password 廃止以外で回帰していないことを確認する
- **品質ゲート**: `mise run migrate:dry-run`、`mise run migrate:testing`、`mise run api:ecs`、`mise run api:phpstan`、`mise run api:arkitect`、`mise run api:test`、`mise run contract:format:check`、`mise run contract:test`、`mise run admin:lint`、`mise run admin:style`、`bunx tsc --noEmit -p src/admin/tsconfig.json` を通す
