# Execution Plan — Plan

## Title

管理画面のパスキーログイン実装

## Status

completed

## Steps

1. ✅ `src/contracts/src/admin/auth/domain.tsp`, `src/contracts/src/admin/auth/transport.tsp`, `src/contracts/src/admin/auth/service.tsp` を変更し、`password` scalar と `LoginRequest` の password 前提を削除する。代わりに `LoginStartRequest(email)`, `LoginStartResponse(authCeremonyId, publicKey)`, `LoginFinishRequest(authCeremonyId, credential)`, `LoginFinishResponse(accessToken, refreshTokenId, refreshToken)` を追加し、`/auth/login/start` と `/auth/login/finish` を定義する。
2. ✅ Step 1 に依存して、契約生成を実行し、`src/contracts/generated/oas/IsekaiObservatory.Admin.v1.yaml`, `src/admin/src/generated/`, `src/server/Generated/` を更新する。生成後に旧 `authenticateServiceLogin` / `LoginRequest` / `LoginResponse` の参照が残っていないか確認する。
3. ✅ `src/server/packages/Auth/Route/AuthRouteMap.php` と `src/server/routes/admin.php` を変更し、旧 `/auth/login` route を削除して `LoginStartController` を `/auth/login/start`、`LoginFinishController` を `/auth/login/finish` に割り当てる。必要な controller import と route enum は `RegisterStart` / `RegisterFinish` と同じ命名規則に揃える。
4. ✅ `src/server/packages/Auth/Application/Admin/UseCase/Login/` に `LoginStartInputData`, `LoginStartOutputData`, `LoginStartUseCase`, `LoginFinishInputData`, `LoginFinishUseCase` を追加する。`LoginStartUseCase` は `AdminUserRepositoryInterface::findByEmail()` で管理ユーザーを取得し、`AdminUserPasskeyRepositoryInterface::findByAdminUserId()` で passkey を取得し、`PasskeyAuthenticatorInterface::startAuthentication()` の options を `PasskeyCeremonyStoreInterface` へ type `login` として保存する。
5. ✅ Step 4 に依存して、`LoginFinishUseCase` で `PasskeyCeremonyStoreInterface::pull()` した state が type `login` であることを検証し、credential の id に対応する `AdminUserPasskeyRepositoryInterface::findByCredentialId()` の結果が state の `adminUserId` と一致することを確認する。その後 `PasskeyAuthenticatorInterface::finishAuthentication()` を呼び、成功時だけ `AdminUserPasskey::withCounter()` と `AdminUserPasskeyRepositoryInterface::update()` で `sign_count` / `last_used_at` を更新する。続けて `RefreshTokenIssueService`, `AccessTokenIssueService`, `RefreshTokenRepositoryInterface`, `AuditLogRecorderInterface` を `LoginFinishUseCase` に注入し、token 発行・refresh token 保存・ログイン監査ログ記録をこの use case 内で行う。
6. ✅ `src/server/packages/Auth/Domain/Models/PasskeyCeremonyState.php` を必要最小限で拡張し、login state では登録専用の `token` / `name` を空文字などで運ばずに済む nullable フィールドへ変更する。`RegisterStartUseCase` / `RegisterFinishUseCase` は既存登録フローの挙動が変わらないよう、register state の必須値として扱う。
7. ✅ `src/server/app/Http/Controllers/Api/Admin/V1/Auth/` と `src/server/app/Http/Presenters/Api/Admin/V1/Auth/` に `LoginStartController`, `LoginStartPresenter`, `LoginFinishController`, `LoginFinishPresenter` を追加し、旧 `LoginController` / `LoginPresenter` は削除する。start は `authCeremonyId` と `publicKey` を返し、finish は `LoginFinishUseCase` の token payload を返す。認証失敗系は token を発行しない 400/401/422 として BFF/UI が扱える応答に揃える。
8. ✅ `src/admin/src/server/routes/auth.ts` と `src/admin/src/server/routes/auth.test.ts` を変更し、BFF の `/auth/login` は email だけを受け取り `authenticateServiceLoginStart` を呼ぶ route と、`authCeremonyId` / `credential` を受け取り `authenticateServiceLoginFinish` を呼んで既存と同じ session / csrf cookie を設定する route に分割する。password schema、password 転送、旧 `authenticateServiceLogin` mock を削除する。
9. ✅ `src/admin/src/components/auth/LoginForm.tsx` を変更し、password 入力欄を削除する。email 送信後に login start BFF を呼び、返却された `publicKey` を `authenticatePasskey()` に渡し、得られた credential を login finish BFF に送る。401/400 や WebAuthn 例外時の表示文言は passkey 認証失敗として扱い、password という文言を残さない。
10. ✅ サーバ側テストを更新・追加する。`src/server/tests/Feature/Api/Admin/V1/Auth/LoginTest.php` は login start/finish の正常系、未登録 email、passkey 未登録、ceremony 不一致、credential id 不一致、検証失敗時の DB 非更新を検証する内容へ置き換える。旧 password login 用の `LoginUseCaseTest` は削除し、feature test で `LoginFinishUseCase` が token 発行・refresh token 保存・ログイン監査ログ記録・passkey 更新タイミングを担うことを確認する。

## Decision Log

- 2026-05-23: PR #716 のコミットは取り込まず、現行 `feature/passkey` にある登録基盤と `PasskeyAuthenticator::startAuthentication()` / `finishAuthentication()` を使ってログイン差分だけを設計する。登録基盤が既に再適用されており、履歴ごとの merge / cherry-pick は不要な衝突と巻き戻しリスクがあるため。
- 2026-05-23: 旧 `POST /auth/login` を email/password 互換で残さず、`POST /auth/login/start` と `POST /auth/login/finish` に分ける。WebAuthn は challenge 発行と assertion 検証が別 ceremony であり、既存登録 API の start/finish と揃える方が contract と BFF の責務が明確になるため。
- 2026-05-23: 旧 password login は削除予定のため、既存 `LoginUseCase` は再利用せず削除対象とする。token 発行・refresh token 保存・ログイン監査ログ記録は `LoginFinishUseCase` に直接実装し、use case 同士の依存や不要な共通化サービスを避ける。
- 2026-05-23: WebAuthn 検証と credential id / admin user id 一致確認が成功するまで `admin_user_passkeys` と refresh token は更新しない。Acceptance Criteria の失敗時非更新を満たし、検証失敗による副作用を防ぐため。
- 2026-05-23: 計画の検証欄にある `generate:server` / `generate:client:admin` は現在の mise task には存在しないため、実際の生成には `contract:compile:admin` / `api:generate` / `admin:generate` を使う。

## Validation

- Acceptance Criteria 1, 7: `src/admin/src/components/auth/LoginForm.tsx` の UI から password input と password 送信が消えていることを確認し、`mise run admin:lint src/admin/src/components/auth/LoginForm.tsx src/admin/src/server/routes/auth.ts src/admin/src/server/routes/auth.test.ts` を実行する。
- Acceptance Criteria 2: `mise run api:test src/server/tests/Feature/Api/Admin/V1/Auth/LoginTest.php` で、登録済み passkey を持つ email の login start が `authCeremonyId` と `publicKey` を返すこと、未登録 email と passkey 未登録ユーザーが token を発行しないことを確認する。
- Acceptance Criteria 3: `src/admin/src/server/routes/auth.test.ts` で login finish 成功時に `accessToken`, `refreshTokenId`, `refreshToken` が session store に保存され、session / csrf cookie が発行されることを確認する。併せて `bun test src/admin/src/server/routes/auth.test.ts` を実行する。
- Acceptance Criteria 4: `mise run api:test src/server/tests/Feature/Api/Admin/V1/Auth/LoginTest.php` で login finish 成功時に対象 `admin_user_passkeys.sign_count` / `last_used_at` が更新され、監査ログの `login` action が記録されることを確認する。
- Acceptance Criteria 5, 6: 同 Feature test で存在しない/期限切れ/種別不一致 ceremony、credential id 不一致、WebAuthn 検証例外時に 400/401 系となり、refresh token が保存されず passkey の `sign_count` / `last_used_at` が変わらないことを確認する。
- Contract / 生成物: `mise run contract:compile:admin`, `mise run api:generate`, `mise run admin:generate`, `mise run api:phpstan packages/Auth app/Http/Controllers/Api/Admin/V1/Auth app/Http/Presenters/Api/Admin/V1/Auth tests/Feature/Api/Admin/V1/Auth`, `mise run api:ecs packages/Auth app/Http/Controllers/Api/Admin/V1/Auth app/Http/Presenters/Api/Admin/V1/Auth tests/Feature/Api/Admin/V1/Auth`, `mise run admin:lint` を実行する。
