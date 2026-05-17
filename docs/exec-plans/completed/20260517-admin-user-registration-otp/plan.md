# Execution Plan — Plan

実装計画（どう進めるか）を書く。問題定義は同ディレクトリの `issue.md` を参照する。

## Title

CLI 発行の登録トークンを用いた管理画面ユーザー登録導線

## Status

completed

## Steps

1. ✅ `src/contracts/src/admin/auth/service.tsp` と `src/contracts/src/admin/auth/transport.tsp` に `POST /auth/register` を追加し、登録フォームが送る `name` `email` `password` `registrationToken` を受ける契約を定義する。登録成功時は新しい認証セッションを発行せず 204 応答に揃え、`src/contracts/generated/oas/IsekaiObservatory.Admin.v1.yaml`、`src/server/Generated`、`src/admin/src/generated/*` を再生成して以降の実装の土台にする。
2. ✅ Auth ドメイン配下に登録用トークンの永続化を追加する。`src/server/database/atlas/schemas/admin-registration-tokens.my.hcl` と `src/server/database/atlas/atlas.hcl` にテーブルを追加し、`src/server/app/Models/Auth/AdminRegistrationToken.php`、`src/server/packages/Auth/Domain/Models/AdminRegistrationToken/*`、`src/server/packages/Auth/Infrastructures/AdminRegistrationToken/*`、`src/server/app/Providers/Domain/AuthServiceProvider.php` でハッシュ化トークン・発行対象メールアドレス・有効期限・消費状態を扱えるようにする。永続化の確認用に `src/server/tests/Integration/Auth/Infrastructures/AdminRegistrationTokenRepositoryTest.php` を追加する。
3. ✅ 既存 CLI の管理ユーザー作成ロジックを Web 登録からも再利用できる形に寄せる。`src/server/packages/AdminUser/Application/Cli/UseCase/Create/CreateUseCase.php` からユーザー生成と保存の共通部分を `src/server/packages/AdminUser/Application/Service/RegisterAdminUserService.php` に抽出し、`AdminUserIntegrityService` と `AdminUserRepositoryInterface` を使う現在のルールをそのまま共有する。既存の `src/server/tests/Unit/AdminUser/Application/Cli/UseCase/CreateUseCaseTest.php` を更新し、必要なら `src/server/tests/Unit/AdminUser/Application/Service/RegisterAdminUserServiceTest.php` を追加する。
4. ✅ CLI から許可対象向け登録トークンを発行する経路を追加する。`src/server/packages/Auth/Application/Cli/UseCase/IssueAdminRegistrationToken/*`、`src/server/app/Console/Commands/Auth/IssueAdminRegistrationTokenCommand.php` を追加し、`admin:invite` コマンドで発行対象メールアドレスと一般/特権の別を受け取って平文トークンを一度だけ出力し、保存時には Step 2 のリポジトリにハッシュのみ保持する。挙動確認として `src/server/tests/Feature/Console/Commands/Auth/IssueAdminRegistrationTokenCommandTest.php` を追加する。
5. ✅ 管理 API の `auth` 配下に登録エンドポイントを実装する。`src/server/packages/Auth/Application/Admin/UseCase/Register/*`、`src/server/app/Http/Controllers/Api/Admin/V1/Auth/RegisterController.php`、`src/server/app/Http/Presenters/Api/Admin/V1/Auth/RegisterPresenter.php`、`src/server/packages/Auth/Route/AuthRouteMap.php`、`src/server/routes/admin.php` を追加・更新し、登録トークンの存在・未使用・未失効・メールアドレス一致を検証したうえで Step 3 の共通サービスから管理ユーザーを作成し、成功時に登録トークンを消費済みにする。API の成立確認として `src/server/tests/Feature/Api/Admin/V1/Auth/RegisterTest.php` を追加する。
6. ✅ 管理画面 BFF に未認証の登録ルートを追加する。`src/admin/src/server/routes/auth.ts` に `register` ハンドラを追加して `authenticateServiceRegister` を呼び出し、ログイン時と違ってセッション Cookie を発行せず API エラーだけをそのまま返す。必要なら `src/admin/src/server/index.ts` の公開ルート構成を最小限調整し、`src/admin/src/server/routes/auth.test.ts` を追加してリクエスト転送と Cookie 非発行を確認する。
7. ✅ WebUI の登録導線を追加する。`src/admin/src/pages/auth/register.astro` と `src/admin/src/components/auth/RegisterForm.tsx` を新規追加し、`src/admin/src/components/auth/LoginForm.tsx` と `src/admin/src/pages/auth/login.astro` を更新して登録ページへの導線を出す。登録フォームでは `name` `email` `password` `registrationToken` を送信し、422/400 の表示は既存 `createFormErrors` を使って処理し、成功時はフラッシュを出して `/auth/login` へ戻す。
8. ✅ 生成物・スキーマ・API・BFF・UI の成立をまとめて検証する。`mise run api:generate`、`mise run admin:generate`、`mise run migrate:dry-run`、`mise run api:test src/server/tests/Feature/Console/Commands/Auth/IssueAdminRegistrationTokenCommandTest.php src/server/tests/Feature/Api/Admin/V1/Auth/RegisterTest.php`、`bun test src/admin/src/server/routes/auth.test.ts`、`mise run api:phpstan src/server/app/Http/Controllers/Api/Admin/V1/Auth src/server/packages/Auth src/server/packages/AdminUser/Application/Service`、`mise run admin:lint src/admin/src/components/auth src/admin/src/pages/auth src/admin/src/server/routes/auth.ts` を実行し、追加した登録導線だけを確認する。

## Decision Log

- 2026-05-17: 登録 API は `auth` 配下の `POST /auth/register` として追加し、`admin-users` API には新しい登録責務を持ち込まない。認証前の公開導線であることと、一覧系 API と責務を分離したいから。
- 2026-05-17: 登録成功時は自動ログインせず 204 を返し、WebUI は完了後に既存ログイン画面へ遷移する。セッション Cookie と CSRF Cookie の発行責務を既存 `/auth/login` に閉じ込めたほうが BFF の差分が小さいから。
- 2026-05-17: 登録トークンは平文を保存せず、発行対象メールアドレスと有効期限・消費状態を持つ独立テーブルで管理する。許可した相手以外への横流しと再利用を防ぎつつ、監査しやすい境界にしたいから。
- 2026-05-17: 管理ユーザー生成ルールは `AdminUserIntegrityService` を使う既存経路を共通サービスへ抽出して再利用する。CLI 作成と Web 登録で重複した検証ロジックを持たせないため。

## Validation

- 「管理者が CLI から登録トークンを発行でき、発行結果から登録に必要なワンタイム値を取得できる」: `src/server/tests/Feature/Console/Commands/Auth/IssueAdminRegistrationTokenCommandTest.php` で `admin:invite` のコマンド出力に平文トークンが含まれ、DB にはハッシュだけと指定ロールが保存されることを確認する。
- 「未認証ユーザーが WebUI の登録フォームから名前・メールアドレス・パスワード・登録トークンを送信できる」: `src/admin/src/server/routes/auth.test.ts` と `src/admin/src/components/auth/RegisterForm.tsx` の実装で `/api/auth/register` への送信項目が 4 項目そろっていることを確認し、`mise run admin:lint` で型・構文整合を確認する。
- 「サーバーは登録トークンが有効な場合のみ管理ユーザーを作成し、使用済みまたは無効な登録トークンでは登録を拒否する」: `src/server/tests/Feature/Api/Admin/V1/Auth/RegisterTest.php` で成功・期限切れ・使用済み・メール不一致・重複メールをそれぞれ検証する。
- 「登録トークンを使った登録導線は、既存の管理ユーザー作成ドメインロジックと整合し、重複したユーザー作成ルールを新設しない」: `CreateUseCase` が共通サービスを呼ぶようにしたうえで、既存 `CreateUseCaseTest` と新規共通サービステストで同一のバリデーション結果になることを確認する。
- 「登録 API は `auth` 配下に追加され、既存の `admin-users` API に新しい登録責務を持ち込まない」: 差分が `src/contracts/src/admin/auth/*`、`src/server/routes/admin.php` の `auth` グループ、`src/admin/src/server/routes/auth.ts` に閉じ、`src/contracts/src/admin/admin-users/service.tsp` と `src/admin/src/server/routes/admin-users.ts` に登録処理を追加していないことをレビューで確認する。
- 「管理画面 BFF / API / CLI の追加部分に対するテストが追加され、関連する静的検査またはテストで成立を確認できる」: Step 8 のコマンド一式を通し、生成・Feature テスト・Bun テスト・PHPStan・ESLint が成功することを完了条件にする。
