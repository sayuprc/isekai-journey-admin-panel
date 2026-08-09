# Execution Plan — Plan

実装計画。問題定義は同ディレクトリの `issue.md` を参照する。

## Title

管理ユーザー登録トークンを使った WebUI 登録導線の追加

## Status

completed

## Steps

1. ✅ **コントラクト追加** (`src/contracts/src/admin/auth/`)
   - `domain.tsp`: 平文の登録トークン用スカラー `registrationToken`（`@secret string`）を追加。
   - `transport.tsp`: `RegisterRequest { token: registrationToken, email: emailAddress, name: adminUserName, password: password }` と `RegisterResponse`（`LoginResponse` と同形：`accessToken` / `refreshTokenId` / `refreshToken`）を追加。
   - `service.tsp`: `AuthenticateService` に `@route("/register")` で `register(@body request: RegisterRequest): RegisterResponse | BadRequest | Unauthorized | Unprocessable | ServerError;` を追加。
   - `mise run contract:format:check` / `contract:test` で確認。

2. ✅ **生成物の更新**
   - `mise run admin:generate` を実行し、`src/server/Generated/` と `src/admin/src/generated/` を更新（手動編集しない）。

3. ✅ **Domain 層拡張** (`src/server/packages/AdminUser/Domain/`)
   - `Models/RegistrationToken/RegistrationTokenRepositoryInterface.php` に以下を追加:
     - `findByEmailForUpdate(Email $email): ?RegistrationToken` — email で該当行を `SELECT ... FOR UPDATE` で取得（行ロックして二重消費を防ぐ）。トークン照合は `TokenHasherInterface::verify` で行う（bcrypt のため `findByHashedToken` 方式は使えない）。
     - `save(RegistrationToken $token): RegistrationToken` — 既存の発行用 `save` と同シグネチャ。新規・既存いずれも upsert 的に永続化し、`consume()` 済みインスタンスもこの経路で保存する（消費専用メソッドは設けない）。
   - `Models/RegistrationToken/RegistrationToken.php` に `consume(): self`（status を Consumed に変えた新インスタンスを返す）と、`isAvailable(DateTimeImmutable $now): bool`（`ConsumptionStatus::isAvailable()` と `ExpiredAt::isExpired($now)` を統合した可否判定）を追加。期限判定単独の `isExpired` は持たず `ExpiredAt` 側に委譲する。
   - `Services/RegistrationToken/RegistrationTokenConsumeService.php` を新規追加し、`verify(string $plainToken, Email $email): Result<RegistrationToken, DomainError>` のシグネチャで、リポジトリ検索 → トークン照合 → `isAvailable` 判定の順に検証する。失敗時はユーザー列挙対策で全て同一の `BusinessRuleViolationError('token_not_found')` を返す。

4. ✅ **Infrastructure 層拡張** (`src/server/packages/AdminUser/Infrastructures/RegistrationToken/RegistrationTokenRepository.php`)
   - `findByEmailForUpdate`: `App\Models\AdminUser\RegistrationToken` で `where('email', $email->value)->lockForUpdate()->first()` し、関連 `permissions` をまとめてロードして `RegistrationToken::reconstruct(...)` で再構成。`lockForUpdate` はトランザクション内からの呼び出しが前提。
   - `save`: 既存の発行用 insert 実装を踏襲しつつ、消費済みインスタンスの保存にも対応するため upsert に拡張する（`admin_user_registration_token_id` をキーに `token` / `email` / `role` / `expired_at` / `status` / `updated_at` を更新、permissions は同 ID 既存行があれば差し替えない）。命名・整形は既存 `RegistrationTokenRepository::save` のスタイル（`UuidConverterInterface`・`$token->toArray()` 経由）に合わせる。

5. ✅ **Application UseCase 追加** (`src/server/packages/AdminUser/Application/Admin/UseCase/Register/`)
   - `RegisterInputData.php`（`string $plainToken, string $email, string $name, string $plainPassword`。`plainToken` / `plainPassword` は `#[SensitiveParameter]`）。
   - `RegisterOutputData.php`（`AccessToken $accessToken, string $refreshTokenId, string $plainRefreshToken`。`AdminUser` は Presenter 側で必要ないため含めない）。
   - `RegisterUseCase.php`: 依存は `TransactionInterface` / `HasherInterface` / `AdminUserRepositoryInterface` / `AdminUserIntegrityService` / `RegistrationTokenConsumeService` / `RegistrationTokenRepositoryInterface` / `RefreshTokenIssueService` / `AccessTokenIssueService` / `RefreshTokenRepositoryInterface` の 9 つ。`TransactionInterface::scope` 内で以下を順次実行し、エラーは `ResultType\Err` に詰める:
     1. `Email::create($inputData->email)` で値オブジェクトを構築。失敗は `InvalidInputError`（422）へ。
     2. `RegistrationTokenConsumeService::verify($plainToken, $email)` で平文トークン → 検証済み `RegistrationToken` を取得（不一致/期限切れ/消費済みは `BusinessRuleViolationError('token_not_found')` → 400）。
     3. `AdminUserIntegrityService::prepareForCreate($name, $token->email->value, $token->role->value, $token->permissions->toArray())` で `AdminUser` を組み立て、email 衝突を検出。
     4. `HashedPassword::create($hasher->hash($plainPassword))` を構築。
     5. `AdminUserRepositoryInterface::register()` で永続化。
     6. `RegistrationTokenRepositoryInterface::save($token->consume())` で `status = Consumed` のインスタンスを永続化。
     7. `RefreshTokenIssueService::issue($adminUser->adminUserId->value)` で Refresh Token を発行し、`AccessTokenIssueService::issue($refreshToken->refreshTokenId->value)` で Access Token を発行、`RefreshTokenRepositoryInterface::save($refreshToken)` で Refresh Token を永続化する（`LoginUseCase` は呼ばない。トランザクション原子性を確保するため、Auth パッケージのドメインサービスを直接利用する）。
     8. `Ok(new RegisterOutputData($accessToken, $refreshToken->refreshTokenId->value, $plainRefreshToken))` を返す。
   - 既存パターン（`IssueRegistrationTokenUseCase` / `CreateUseCase`）と同じ `handleError` を実装（`DomainValidationError` / `EntityRuleViolationError` → `InvalidInputError`、`BusinessRuleViolationError` → `BusinessLogicError`）。

6. ✅ **HTTP 層追加**
   - `src/server/app/Http/Controllers/Api/Admin/V1/Auth/RegisterController.php`: `RegisterController` を新規追加。`RegisterUseCase` の戻り値（`RegisterOutputData`）だけを `RegisterPresenter` に渡す。`LoginUseCase` には依存しない（登録経路内で Token 発行までを完了させる）。
   - `src/server/app/Http/Presenters/Api/Admin/V1/Auth/RegisterPresenter.php`: `RegisterOutputData` から `OpenAPI\Client\Model\RegisterResponse` を組み立てる。`InvalidInputError` → 422 はバリデーション固有メッセージを返し、それ以外（`BusinessLogicError` 含む）は 400 で「登録に失敗しました。入力内容を確認してください。」の固定文言に丸める（ユーザー列挙対策）。
   - `src/server/packages/Auth/Route/AuthRouteMap.php` に `case Register = 'register';` を追加。
   - `src/server/routes/admin.php` の `auth` グループに `Route::post('/register', [RegisterController::class, 'handle'])->name(AuthRouteMap::Register);` を追加。

7. ✅ **admin BFF 拡張** (`src/admin/src/server/routes/auth.ts`)
   - 既存 `/login` ハンドラと同じ `Elysia` インスタンスに `.post('/register', ...)` を追加し、`authenticateServiceRegister` を呼んでセッション/CSRF Cookie を発行（`storeSessionCredential` と Cookie 設定は `setAuthCookies` ヘルパで共通化）。
   - `body` は `t.Object({ token: t.String(), email: t.String(), name: t.String(), password: t.String() })`。
   - `src/admin/src/server/routes/auth.test.ts` に登録系のテストを追加（`/login` のテストパターンに揃える）。なければ新規作成。

8. ✅ **フロント追加** (`src/admin/src/`)
   - `pages/auth/register.astro`: `login.astro` を元にレイアウト・タイトル・`<RegisterForm client:load />` 配置。
   - `components/auth/RegisterForm.tsx`: `LoginForm.tsx` を元に `token` / `email` / `name` / `password` 入力を持つフォームを実装。`client.api.auth.register.post({...})` を呼び、200 で `setFlash('登録しました')` → `window.location.href = '/song-types'`。エラーは 400 を `FormError`（固定文言「登録に失敗しました。トークンを確認してください。」）、422 を `handleError` 経由で `getFieldError` に振り分ける。
   - 専用フォームスキーマは追加していない（フォーム要素の `required` と BFF/API 側のバリデーションで担保）。

9. ✅ **PHP テスト追加**
   - Unit (`src/server/tests/Unit/AdminUser/Application/Admin/UseCase/Register/RegisterUseCaseTest.php`): 成功・各エラー分岐（email バリデーション / トークン不一致 / 期限切れ / 消費済み / email 衝突）を `php-unit-test-creator` 規約で網羅。
   - Integration (`src/server/tests/Integration/AdminUser/Infrastructures/RegistrationToken/RegistrationTokenRepositoryTest.php`): `findByEmailForUpdate` と、`consume()` 済みインスタンスを `save` した際に `status = Consumed` で更新されることを `DatabaseTestCase` で検証（既存があればケース追加）。
   - Feature (`src/server/tests/Feature/Api/Admin/V1/Auth/RegisterTest.php`): `POST /api/admin/v1/auth/register` の 200 / 422 / 400 ケースと、`admin_users` 行追加・`admin_user_registration_tokens.status = Consumed` を `php-feature-test-creator` 規約で検証。

10. ✅ **検証コマンド**（後述 Validation 節と同じ）を全部通し、PR を作成。

## Decision Log

- 2026-05-17: 平文トークンから行を引く方式として、**email を入力せず token のハッシュ値（一意）で直接検索**する。`HashedTokenValue` は SHA-256 等の一意性が高いハッシュ前提なので、メールアドレスを画面で入力させずに済み、UX 上有利。リポジトリには `findByHashedToken` を追加。
- 2026-05-17 (revised): `TokenHasher` 実装が bcrypt（`Hash::make`）由来でハッシュ値が非決定的なため、`findByHashedToken` ではハッシュ前後の値が一致せず行を引けない問題が判明。方式を変更し、画面で **email も入力させて email で `RegistrationToken` 行を取得 → `TokenHasherInterface::verify` で平文トークンを照合** する。リポジトリは `findByEmail(Email)` を提供する。ユーザー列挙対策のため、行不存在 / トークン不一致 / 期限切れ / 消費済みはすべて同一の `BusinessRuleViolationError('token_not_found')` で表現する。
- 2026-05-17: トークン消費は `RegistrationTokenRepositoryInterface::save()` に一本化し、同一トランザクション内で直前に未消費を確認したうえで `consume()` 済みインスタンスを upsert で保存する。`RegistrationToken` の消費・新規 `AdminUser` 作成・role/permissions 反映は `TransactionInterface::scope` 内に閉じ込め原子性を確保。
- 2026-05-17: 登録後のログインセッション化は **Auth パッケージの既存 `LoginUseCase` を `RegisterController` から再呼び出し** する方式を採る。トークン発行ロジックの二重実装を避け、`LoginPresenter` 相当のレスポンス形を保つため。`LoginController` で行うパスワード認証は登録フローでは不要（トークン消費が本人確認に相当）。
- 2026-05-18 (revised): 上記「`LoginUseCase` 再利用」方針を撤回。`RegisterUseCase` が `AdminUser` 作成 + トークン消費に成功した後でコントローラから別 UseCase（別トランザクション）を呼ぶ構造では、Login 失敗時に AdminUser だけ作成済みという不整合を生む。これを回避するため、`RegisterUseCase` の同一 `TransactionInterface::scope` 内で Auth パッケージのドメインサービス（`RefreshTokenIssueService` / `AccessTokenIssueService`）と `RefreshTokenRepositoryInterface` を直接利用し、Refresh Token / Access Token の発行までを 1 単位で完結させる。`LoginUseCase` 本体は変更しない（既存テスト・既存呼び出しはそのまま）。登録時の監査ログ（`AuditAction::Login`）は記録しない（最小化のため。必要になったら `Register` 相当の AuditAction を別途追加する）。
- 2026-05-17: トークン検証の失敗（不一致 / 期限切れ / 消費済み）はビジネスルール違反として `BusinessLogicError` → 400 で返し、UI 側で同じ汎用エラー表示にする。ユーザー列挙攻撃を避けるためメッセージは細分化しない（理由を細かく開示しない）。
- 2026-05-18: 上記方針を Presenter 実装側にも反映。`RegisterPresenter` は `BusinessLogicError` の `message` をそのまま返さず、400 のレスポンスメッセージを固定文言（「登録に失敗しました。入力内容を確認してください。」）に丸める。これにより email 衝突 / トークン不一致 / 期限切れ / 消費済みのいずれであっても外部から区別できない（ユーザー列挙対策）。422（`InvalidInputError`）はフィールド別バリデーションメッセージを返す既存挙動を維持する。
- 2026-05-17: 新規ドメインサービス名は `RegistrationTokenConsumeService` とし、`RegistrationTokenIssueService` と対になる責務（消費可否を含む検証）に絞る。実際の DB 状態変更は UseCase が `RegistrationToken::consume()` の結果を `RegistrationTokenRepositoryInterface::save()` に渡すことで行う。
- 2026-05-17: `markConsumed` を Repository に置かず、`save` 経由で `consume()` 済みインスタンスを永続化する方針に変更。Domain モデル側で状態遷移を表現し、Repository は永続化のみを担う（CQS とドメイン責務の分離を優先）。
- 2026-05-17: API 用語は CLI 側の `AdminUser\RegistrationToken` に揃える（`feature/passkey-admin-registration-token` ブランチの命名は参照しない）。エンドポイントは `POST /auth/register`。
- 2026-05-18: 同時リクエストによる二重 consume を防ぐため、消費フロー専用に `RegistrationTokenRepositoryInterface::findByEmailForUpdate(Email)` を追加し `SELECT ... FOR UPDATE` で行ロックする。読み取り専用利用との分離と意図の明示を優先し、汎用の `findByEmail` は削除した（呼び出し元は `RegistrationTokenConsumeService` のみだったため）。Laravel の `lockForUpdate` はトランザクション外では効かないので、`RegisterUseCase::handle` の `TransactionInterface::scope` 内から呼ばれる前提を維持する。
- 2026-05-18: 期限判定単独の `RegistrationToken::isExpired` は導入せず、消費可否判定を `RegistrationToken::isAvailable(DateTimeImmutable $now)` に統合した（`ConsumptionStatus::isAvailable()` と `ExpiredAt::isExpired($now)` を合成）。呼び出し側（`RegistrationTokenConsumeService`）は単一メソッドだけを参照すれば良く、消費判定と期限判定の組み合わせ漏れを構造的に防げる。

## Validation

Acceptance Criteria 毎の検証手順:

- **AC1 (200 / `admin_users` 作成 / トークン消費)**: `RegisterTest` の成功ケースで `POST /api/admin/v1/auth/register` 後に `admin_users` 行と `admin_user_registration_tokens.status = Consumed` をアサート。
- **AC2 (role/permissions 反映)**: 同 Feature テストで `admin_user_roles` / `admin_user_permissions` 相当（`AdminUser` 永続化結果）にトークン発行時の値が引き継がれていることをアサート。
- **AC3 (BFF セッション化)**: `src/admin/src/server/routes/auth.test.ts` の登録ケースで `Set-Cookie` に `session` / `csrf` が含まれ、Redis に `storeSessionCredential` 経由でクレデンシャル（`accessToken` / `refreshTokenId` / `refreshToken` / `csrfToken`）が入ることを `bun:test` で確認。
- **AC4 (失敗ケース)**: `RegisterTest` に以下のケースを追加:
  - トークン不一致（行は存在するがハッシュ照合に失敗）→ 400、DB 無変更。
  - `expired_at` 経過 → 400、DB 無変更。
  - `status = Consumed` → 400、DB 無変更。
  - バリデーション違反（不正な email / 空 name / 短すぎる password）→ 422、DB 無変更。
  - email 衝突（既存 `admin_users` に同 email）→ 400、DB 無変更。
- **AC5 (回帰なし)**: `mise run api:test` で既存 `LoginTest` / `RefreshTest` / `IssueRegistrationTokenTest` / `CreateTest` がグリーンであることを確認。
- **AC6 (品質ゲート)**:
  - `mise run api:ecs`
  - `mise run api:phpstan`
  - `mise run api:arkitect`
  - `mise run api:test`
  - `mise run contract:format:check`
  - `mise run contract:test`
  - `cd src && bun --filter admin lint:check`
  - `cd src && bun --filter admin style:check`
  - `cd src && bun --filter admin test`（BFF vitest が存在する場合）
