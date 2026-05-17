# Execution Plan — Plan

実装計画（どう進めるか）を書く。問題定義は同ディレクトリの `issue.md` を参照する。

## Title

管理画面の招待トークンによるユーザー登録機能

## Status

completed

## Steps

1. ✅ **DB スキーマと永続化モデルを追加する**
   - `src/server/database/atlas/schemas/admin-user-invitations.my.hcl` を新規作成。カラムは `invitation_id binary(16)` (PK)、`token_hash varchar(255)` (UNIQUE)、`role tinyint unsigned`、`expires_at datetime`、`consumed_at datetime null`、`created_at datetime`、`updated_at datetime`。権限は `admin-user-invitation-permissions.my.hcl`（`invitation_id`, `permission` の複合 PK）として別ファイルで定義する。
   - `src/server/app/Models/AdminUser/AdminUserInvitation.php` と `AdminUserInvitationPermission.php` を Eloquent モデルとして追加（既存 `AdminUser` モデル群と同じスタイル）。
   - 既存の atlas マイグレーション生成タスクでマイグレーションを生成する。

2. ✅ **Domain 層に招待トークンモデルとサービスを追加する**
   - `src/server/packages/AdminUser/Domain/Models/Invitation/` 配下に以下を追加:
     - `InvitationId.php`（uuid）、`PlainToken.php`（生成・乱数 32byte hex）、`HashedToken.php`、`ExpiresAt.php`、`ConsumedAt.php`、`Invitation.php` 集約、`InvitationRepositoryInterface.php`。
   - `Invitation` は `consume(DateTimeImmutable $now): Result` を持ち、消費済み / 期限切れ時は `BusinessRuleViolationError` を返す。
   - `src/server/packages/AdminUser/Domain/Services/TokenHasherInterface.php` を追加し、`hash(PlainToken): HashedToken` を定義する（password 用 `HasherInterface` とは別にして関心を分離）。

3. ✅ **Infrastructures に招待トークン実装を追加する**
   - `src/server/packages/AdminUser/Infrastructures/InvitationRepository.php` で `save(Invitation, HashedToken)` / `findByHashedToken(HashedToken)` / `markConsumed(Invitation)` を実装。
   - `src/server/packages/AdminUser/Infrastructures/TokenHasher.php` を追加（`hash_hmac('sha256', $token, config('app.key'))` 等の確定的ハッシュ。lookup 可能にするため password 用 bcrypt とは区別する）。
   - 既存 Provider（`app/Providers/` の Repository バインド箇所）に `InvitationRepositoryInterface` / `TokenHasherInterface` のバインドを追加する。

4. ✅ **Cli ユースケースを追加して `admin:invite` コマンドを実装する**
   - `src/server/packages/AdminUser/Application/Cli/UseCase/Invite/InviteInputData.php` / `InviteOutputData.php` / `InviteUseCase.php` を追加。入力は `role`, `permissions`, `expiresInHours`（既定 24h）。出力は `PlainToken` と `ExpiresAt`。
   - `src/server/app/Console/Commands/AdminUser/InviteCommand.php` を追加。signature: `admin:invite {--p|privilege} {--e|expires-in-hours=24} {permissions?*}`。成功時に平文トークンと有効期限を `$this->info()` で表示する。
   - 既存 `CreateCommand` と同じ登録経路で Artisan に登録する。

5. ✅ **TypeSpec 契約に登録 API を追加する**
   - `src/contracts/src/admin/admin-users/domain.tsp` に `@secret scalar invitationToken extends string;` を追加。
   - `src/contracts/src/admin/admin-users/transport.tsp` に `RegisterAdminUserRequest`（`token: invitationToken`, `name: adminUserName`, `email: email`, `password: password`）と `RegisterAdminUserResponse`（登録された `AdminUser` を返す）を追加。
   - `src/contracts/src/admin/admin-users/service.tsp` に `@route("/registrations") @post register(@body request: RegisterAdminUserRequest): Created<RegisterAdminUserResponse> | Unauthorized | Unprocessable | ServerError;` を追加。この op は `@useAuth(NoAuth)` で認証を解除する（`auth/service.tsp` の login が無認証になっているパターンに倣う）。
   - `mise run contract:format:check` / `contract:test` / `contract:compile:admin` を実行し、`src/server/Generated/` と `src/admin/src/generated/` の生成物を更新する。

6. ✅ **Admin ユースケース（登録）を追加する**
   - `src/server/packages/AdminUser/Application/Admin/UseCase/Register/` に `RegisterInputData.php` / `RegisterOutputData.php` / `RegisterUseCase.php` を追加。
   - `RegisterUseCase` はトランザクション内で: (1) `TokenHasher` でトークンをハッシュ化 → `InvitationRepository::findByHashedToken` で取得、(2) `Invitation::consume()` で期限・消費済み判定、(3) `AdminUserIntegrityService::prepareForCreate` で `AdminUser` 構築、(4) `HashedPassword` 生成、(5) `AdminUserRepository::register` 実行、(6) `InvitationRepository::markConsumed`。
   - エラーは既存 `CreateUseCase` と同じく `BusinessLogicError` / `InvalidInputError` に変換。トークン無効・期限切れ・消費済みは `BusinessLogicError` として 401 にマップする。

7. ✅ **HTTP Controller / Presenter / Route を追加する**
   - `src/server/app/Http/Controllers/Api/Admin/V1/AdminUser/RegisterAdminUserController.php` と `src/server/app/Http/Presenters/Api/Admin/V1/AdminUser/RegisterPresenter.php` を追加（`ListAdminUserController` / `ListPresenter` の構成を踏襲）。
   - `src/server/packages/AdminUser/Route/AdminUserRouteMap.php` に `case Register = 'admin-users/registrations';` を追加。
   - `src/server/routes/admin.php` の `admin-users` グループ外（`Authenticate` ミドルウェアが掛からない位置）に `Route::post('/admin-users/registrations', [RegisterAdminUserController::class, 'handle'])->name(AdminUserRouteMap::Register);` を追加。`AdminOpenApiValidator` は通す。

8. ✅ **管理画面に登録ページとフォームを追加する**
   - `src/admin/src/pages/auth/register.astro` を追加。`Astro.url.searchParams.get('token')` を取得し `<RegisterForm token={...} client:load />` に渡す。レイアウトは `SimpleLayout`。
   - `src/admin/src/components/auth/RegisterForm.tsx` を追加。`LoginForm.tsx` のスタイル・`createFormErrors` / `createSubmitting` パターンに倣い、`token`（hidden）/ `name` / `email` / `password` を送信。`src/admin/src/server/routes/admin-users.ts` 経由（必要なら新規にプロキシハンドラを追加）または BFF を介して生成済みクライアントで `POST /admin/v1/admin-users/registrations` を呼ぶ。
   - `src/admin/src/schemas/AdminUserRegistration.ts` スキーマを追加（既存 `SongType.ts` のスタイルに合わせる）。
   - 成功時は `/auth/login` に遷移しフラッシュメッセージ「登録しました」を表示。401 はフォームエラー（トークン無効）、422 はフィールドエラーを表示する。

9. ✅ **テストを追加する**
   - Unit: `tests/Unit/AdminUser/Domain/Models/Invitation/InvitationTest.php`（`consume` 成功 / 期限切れ / 二重消費）、`PlainToken` / `HashedToken` バリデーション、`TokenHasher` 単体（同入力で同出力）。
   - Integration: `InvitationRepositoryTest`（`DatabaseTestCase` 継承、`save` / `findByHashedToken` / `markConsumed`）。
   - Feature(Console): `tests/Feature/Console/AdminUser/InviteCommandTest.php`（標準出力にトークン・期限が出ること、DB にハッシュのみ保存されることをアサート）。
   - Feature(Api): `tests/Feature/Api/Admin/V1/AdminUser/RegisterAdminUserTest.php`（正常系で 201 + DB 行、期限切れ / 二重使用 / 不正トークンで 401、入力不正で 422、登録後 `POST /admin/v1/auth/login` が 200）。

10. ✅ **検証とドキュメント更新を行う**
    - `mise run contract:format:check` / `contract:test` / `contract:compile:admin`、`mise run api:ecs` / `api:phpstan` / `api:arkitect` / `api:test` を実行し成功させる。admin の `lint:check` / `style:check` / `build` はユーザー指示によりスキップする（2026-05-17 追記: 既存 build が失敗するため）。
    - `docs/product-specs/` 配下に管理ユーザー招待運用の仕様（既存があれば追記、無ければ `admin-user-invitation.md` を新規）として「`admin:invite` で発行 → 受領者が `/auth/register?token=...` で登録」の運用手順を簡潔に追記する。

## Decision Log

- 2026-05-17: 招待トークンは DB に hash で保存し、lookup できるよう `hash_hmac('sha256', token, APP_KEY)` 等の確定的ハッシュを使う。bcrypt（password 用 `HasherInterface`）は lookup 不可なため別インターフェイス `TokenHasherInterface` を導入する。
- 2026-05-17: 招待トークン発行 API は契約に含めない（CLI 専用 / `issue.md` の Scope に従う）。登録 API のみ TypeSpec に追加する。
- 2026-05-17: 登録 API は未認証で叩く必要があるため、TypeSpec 側で `@useAuth(NoAuth)`、Laravel 側で `Authenticate` ミドルウェアのグループ外に置く（既存 `login` / `refresh` と同じ方針）。
- 2026-05-17: 招待は consume 後も監査のためレコードを残す（`consumed_at` を立てるのみで delete はしない）。同一トークンの 2 回目登録は `consumed_at IS NOT NULL` で弾く。
- 2026-05-17: トークン有効期限の既定は 24 時間。CLI オプション `--expires-in-hours` で上書きできる。
- 2026-05-17: 名前・メールは招待発行時ではなく登録時に本人が入力する。CLI 実行者の入力負荷を下げ、issue の意図（本人が機微情報を設定）を一貫させるため。
- 2026-05-17: atlas は declarative schema 方式のため、Step 1 の「マイグレーションを生成する」相当の作業は不要（HCL 追加と `atlas.hcl` の `table_schemas` への登録で完了）。`mise run migrate:local` / `migrate:testing` で適用する。
- 2026-05-17: トークン生成は Infrastructure 層（既存 `Auth\RandomTokenGenerator` 同様）の責務とし、`AdminUser\Domain\Services\PlainTokenGeneratorInterface` を Domain に置いて DI する。`PlainToken` 自体は値の妥当性検査のみ持つ（生成ロジックは含めない）。
- 2026-05-17: 招待トークンの無効・期限切れ・消費済みは UseCase 層で `BusinessLogicError` ではなく `AuthenticationError` として返す。既存 `ResolvesUseCaseError` トレイトが `AuthenticationError` を 401 に確定的にマップしているため、Presenter にカスタムマッピングを増やさず関心を分離できる。
- 2026-05-17: 契約に `Created<T>` レスポンス型が無かったため `src/contracts/src/admin/lib/response.tsp` に追加した（既存 `Ok<T>` と対称な定義）。
- 2026-05-17: `PlainToken` は `StringValueObject` の `final __construct` を上書きできないため、`SensitiveParameter` 注釈を付けたコンストラクタを置く設計を取りやめ、`isValid` / `getMessage` のオーバーライドのみで形式検査を行うようにした。
- 2026-05-17: ECS フォーマッタが `routes/admin.php` の短い `RegisterAdminUserController` を未使用 import 扱いで除去してしまうため、ルート定義側で完全修飾名 (`\App\Http\Controllers\Api\Admin\V1\AdminUser\RegisterAdminUserController::class`) を直書きしている。
- 2026-05-17: InvitationRepository は save(Invitation) のみ。markConsumed を分けると永続化の都合が Domain に漏れるためレビューで撤回（consume() が消費済み Entity を返し、それを save する流れに統一）。
- 2026-05-17: 登録エンドポイントは /admin-users/registrations から /auth/register へ移設。login/refresh と並ぶ認証系として整理（UseCase は AdminUser package に残す）
- 2026-05-17: register の `@useAuth(NoAuth)` を削除しレスポンスを 200 に変更（login/refresh と同じスタイルに統一）。`Created<T>` ヘルパーは未使用化したため削除

## Validation

- AC「`admin:invite` CLI コマンドが、引数として役割／権限を受け取り、ワンタイムの招待トークン文字列と有効期限を標準出力に表示する」
  - 手動: `docker compose exec api php artisan admin:invite -p` および `docker compose exec api php artisan admin:invite read_admin_user write_admin_user --expires-in-hours=48` で平文トークンと `expires_at` が表示されることを確認。
  - 自動: `mise run api:test -- --filter=InviteCommandTest` が通る。
- AC「招待トークンは DB にハッシュ化して保存され、平文は発行時の標準出力のみで確認できる」
  - `InviteCommandTest` 内で DB の `token_hash` が平文と一致しないこと、`TokenHasher::hash()` 結果と一致することをアサート。
- AC「管理画面に登録ページが存在し、トークン・名前・メール・パスワードを入力して送信すると管理ユーザーが作成される」
  - `RegisterAdminUserTest` 正常系で 201 と DB レコードを検証。
  - 手動: `/auth/register?token=<発行値>` にアクセスして送信し、`admin_users` テーブルに行が増えること。
- AC「同じトークンで 2 回目の登録は失敗する」
  - `RegisterAdminUserTest::test_already_consumed_token_returns_401` を追加。
- AC「有効期限切れ・存在しない・不正なトークンでの登録は API が 401 もしくは 422 を返し、UI にエラーが表示される」
  - Feature テストで 401 を検証。手動でトークン書き換えして送信し、`RegisterForm` のフォームエラー表示を確認。
- AC「登録完了後、ユーザーは既存のログイン API でログインできる」
  - Feature テスト末尾で `POST /admin/v1/auth/login` を叩いて 200 を確認。
- AC「TypeSpec の変更は ... 通り、生成物が更新済み」
  - `mise run contract:format:check && mise run contract:test && mise run contract:compile:admin` がすべて成功。`git status` で `src/server/Generated/` と `src/admin/src/generated/` の差分が含まれていることを確認。
- AC「サーバー側は ... 通り、テストが追加されている」
  - `mise run api:ecs && mise run api:phpstan && mise run api:arkitect && mise run api:test` がすべて成功。
- AC「管理画面は ... 通る」
  - `cd src && bun --filter admin lint:check && bun --filter admin style:check && bun --filter admin build` がすべて成功。
