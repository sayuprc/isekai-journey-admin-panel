# Execution Plan — Plan

実装計画(どう進めるか)を書く。問題定義は同ディレクトリの `issue.md` を参照する

## Title

管理ユーザー登録 WebUI 用の登録トークン発行 CLI

## Status

completed

## Steps

✅ 1. **DB スキーマを追加する**
   - `src/server/database/atlas/schemas/admin-user-registration-tokens.my.hcl` を新規作成する。`refresh-tokens.my.hcl` の構造を踏襲しつつ、`admin_user_id` への外部キーは持たない(まだ管理ユーザーは存在しないため)
   - 列: `admin_user_registration_token_id binary(16)` (PK), `token varchar(255) NOT NULL`(ハッシュ保存), `email varchar(255) NOT NULL`, `role tinyint unsigned NOT NULL`, `expired_at datetime NOT NULL`, `status tinyint unsigned NOT NULL`(未消費=0/消費済=1), `created_at datetime NOT NULL`, `updated_at datetime NOT NULL`
   - `email` には unique index を張らない(消費済みトークンや過去の招待が残るため)。発行時に「同 email の `admin_users` が既存」「同 email の未消費トークンが既存」を UseCase でチェックする
   - 付与予定 permission は別テーブル `admin-user-registration-token-permissions.my.hcl` に切り出す(`admin-user-permissions.my.hcl` の形を参考に)。列: `admin_user_registration_token_id binary(16)`, `permission varchar(255)` の複合 PK、`fk_*_token_id` で CASCADE
   - `src/server/database/atlas/schemas/schema.my.hcl` に新テーブルが取り込まれるか確認し、必要なら追記
✅ 2. **Eloquent モデルを追加する**
   - `src/server/app/Models/AdminUser/RegistrationToken.php` を `App\Models\AdminUser` namespace で追加し、`$table = 'admin_user_registration_tokens'`、`$primaryKey = 'admin_user_registration_token_id'`、`expired_at` などを `immutable_datetime` でキャスト
   - `src/server/app/Models/AdminUser/RegistrationTokenPermission.php` を追加し、`permissions` 用 hasMany / belongsTo を `AdminUser/AdminUser.php`〜`AdminUserPermission.php` と同じ要領で定義
✅ 3. **ドメイン値オブジェクト/モデルを追加する** (`src/server/packages/AdminUser/Domain/Models/RegistrationToken/`)
   - `RegistrationTokenId.php`: `UuidValueObject` 継承(`AdminUserId.php` を参考)
   - `HashedTokenValue.php`: `StringValueObject` 継承(`Auth\...\HashedTokenValue` と同等)
   - `ExpiredAt.php`: `ImmutableDateTimeValueObject` 継承し `isExpired()` を持つ(`Auth\...\ExpiredAt` を参考)
   - `ConsumptionStatus.php`: `Unused = 0` / `Consumed = 1` の enum(`Auth\...\ConsumptionStatus` と同等)
   - `RegistrationToken.php`: コンストラクタ引数 `RegistrationTokenId $id, HashedTokenValue $token, Email $email, Role $role, Permissions $permissions, ExpiredAt $expiredAt, ConsumptionStatus $status`。`Email` は既存 `AdminUser\Domain\Models\Email` を再利用。`reconstruct()` と `toArray()` を実装
   - `RegistrationTokenRepositoryInterface.php`: `save(RegistrationToken $token): RegistrationToken` を最低限定義する(一覧/取得は本タスクでは Non-Scope なので追加しない)
✅ 4. **ドメインサービス: トークン発行ロジック** (`src/server/packages/AdminUser/Domain/Services/RegistrationToken/`)
   - `RegistrationTokenIssueService.php` を追加。`ClockInterface`, `UuidGeneratorInterface`, `RandomTokenGeneratorInterface`, `TokenHasherInterface` を DI。`Auth\Domain\Services\Token\RefreshToken\RefreshTokenIssueService` を参考に `issue(Email $email, int $role, list<string> $permissions): Result<array{token: RegistrationToken, plainToken: string}, DomainError>` を実装。TTL は定数 `TTL_DAY = 7`
   - `TokenHasherInterface` / `RandomTokenGeneratorInterface` は AdminUser パッケージ内(`AdminUser\Domain\Services\RegistrationToken\` 配下)に新規追加する(phparkitect が Auth パッケージへの Domain 依存を禁じているため。Decision Log 参照)
✅ 5. **インフラ実装** (`src/server/packages/AdminUser/Infrastructures/RegistrationToken/`)
   - `RegistrationTokenRepository.php` を追加し `RegistrationTokenRepositoryInterface` を実装。`UuidConverterInterface` を DI。`save()` で `admin_user_registration_tokens` への `insert` と `admin_user_registration_token_permissions` への `insert`(permissions 配列があれば)を行う
✅ 6. **UseCase 層** (`src/server/packages/AdminUser/Application/Cli/UseCase/IssueRegistrationToken/`)
   - `IssueRegistrationTokenInputData.php`: `string $email`, `int $role`, `list<string> $permissions`
   - `IssueRegistrationTokenOutputData.php`: `RegistrationToken $token`, `string $plainToken`
   - `IssueRegistrationTokenUseCase.php`: `TransactionInterface`, `AdminUserRepositoryInterface`(既存 email 衝突チェック用), `RegistrationTokenIssueService`, `RegistrationTokenRepositoryInterface` を DI。`transaction->scope` 内で (a) `Email::create` で VO 構築(失敗時は `DomainValidationError`)、(b) `adminUserRepository->findByEmail` で既存 `admin_users` の衝突チェック(あれば `BusinessRuleViolationError`)、(c) `issueService->issue` → `repository->save` → `Ok`。`handleError()` は `DomainValidationError` / `EntityRuleViolationError` を `InvalidInputError` に、`BusinessRuleViolationError` を `BusinessLogicError` に変換する(`CreateUseCase` に `BusinessRuleViolationError` 分岐を追加した形)
✅ 7. **CLI コマンド**
   - `src/server/app/Console/Commands/AdminUser/InviteCommand.php` を追加
   - `protected $signature = 'admin:invite {email} {--p|privilege} {permissions?*}'`、`$description = '管理ユーザー登録トークンを発行する'`
   - `handle(IssueRegistrationTokenUseCase $useCase): int` で、`CreateCommand` と同じく permission の `Permission::tryFrom` バリデーションを行い、不正なら `Command::FAILURE`
   - 成功時、`OutputData` の `plainToken` を `$this->line($plainToken)` で 1 行だけ標準出力に出す(`info` ではなく `line` にして装飾を付けない)。さらに有効期限などの補助情報を `info` で出力(Decision Log 参照)
✅ 8. **DI バインディング**
   - `src/server/app/Providers/Domain/AdminUserServiceProvider.php` に以下を追加:
     - `AdminUser\Domain\Models\RegistrationToken\RegistrationTokenRepositoryInterface` → `AdminUser\Infrastructures\RegistrationToken\RegistrationTokenRepository`
     - `AdminUser\Domain\Services\RegistrationToken\RandomTokenGeneratorInterface` → `AdminUser\Infrastructures\RegistrationToken\RandomTokenGenerator`
     - `AdminUser\Domain\Services\RegistrationToken\TokenHasherInterface` → `AdminUser\Infrastructures\RegistrationToken\TokenHasher`
   - `InviteCommand` は既存 `CreateCommand` 同様 Laravel の自動発見に任せる
✅ 9. **テスト**
   - Unit: `src/server/tests/Unit/AdminUser/Application/Cli/UseCase/IssueRegistrationTokenUseCaseTest.php`。`CreateUseCaseTest` を参考に、`TransactionInterface` / `AdminUserRepositoryInterface` / `RegistrationTokenIssueService` / `RegistrationTokenRepositoryInterface` を Mockery で差し替え。成功ケース、既存 email 衝突ケース(`BusinessLogicError`)、サービスが `Err(DomainValidationError)` を返すケースを検証
   - Integration: `src/server/tests/Integration/AdminUser/Application/Cli/UseCase/IssueRegistrationTokenUseCaseTest.php`。`DatabaseTestCase` 継承、`app->make` した UseCase で実行 → `admin_user_registration_tokens` が 1 行追加・`token` 列がハッシュ・`role`/`permissions`/`expired_at`/`status` が期待通り、を検証
   - Feature: `src/server/tests/Feature/Console/Commands/AdminUser/InviteCommandTest.php`。`CreateCommandTest` を参考に: 一般ユーザー発行 / 特権 + permissions 付き発行 / 不正 permission 拒否(テーブルに行が追加されないこと)/ 既存 `admin_users` と同 email を指定した場合の拒否 / 出力に平文トークンが含まれる、を確認
✅ 10. **検証コマンドの実行**
    - `mise run api:ecs` / `api:phpstan` / `api:arkitect` / `api:test` をすべて green になるまで実行。phparkitect の Domain → Infra 依存禁止ルールに違反していないかを必ず確認する

## Decision Log

- 2026-05-17: トークン平文生成・ハッシュ化のロジック自体は `Auth\...\RandomTokenGenerator`(`bin2hex(random_bytes(64))`、128 文字)/ `Auth\...\TokenHasher`(`Hash::make` / `Hash::check`)と同等のものを採用する。ただし phparkitect 上 `AdminUser\Domain` から `Auth\Domain` への依存が禁じられているため、interface と実装は AdminUser パッケージ内に新設し、ロジックを duplicate する(下記「実装中の変更」参照)。Auth 側の既存 `Hasher`(パスワード用)はコスト体系が異なるため流用しない
- 2026-05-17: テーブル名は `admin_user_registration_tokens`、permission 中間テーブルは `admin_user_registration_token_permissions` とする。`refresh_tokens` と異なり、発行時点では対応する `admin_users` 行が存在しないため、`admin_user_id` カラム・FK を持たせない。代わりに role/permission を発行時に固定値として保持する
- 2026-05-17: 有効期限のデフォルトは 7 日(`RefreshTokenIssueService` と揃える)。コマンド引数では指定せず、ドメインサービス内の定数で固定する。将来 CLI から上書きしたくなった時に最小コストで足せるよう、サービス側を後から拡張する形に留める
- 2026-05-17: CLI シグネチャは `admin:invite {email} {--p|privilege} {permissions?*}` とし、`CreateCommand` の `--p|privilege` フラグと位置引数 `permissions` の渡し方を踏襲する。`email` は招待時に必須で受け取り、トークン行に保存する。これにより漏洩したトークンを別人が別 email で消費する事故を防ぎ、WebUI 側で email 入力を不要にできる。`name` は WebUI で本人が入力する想定で持たない
- 2026-05-17: `email` の重複チェックは「既存 `admin_users` に同 email がいない」のみ UseCase で行う。未消費トークン同士の重複は許容(招待のやり直し・再発行を将来追加するため)。DB 側に unique 制約は張らない
- 2026-05-17: 平文トークンは `$this->line($plainToken)` で 1 行のみ出力する。`info` だと `<info>` タグで装飾されコピペ事故が起きやすいため。補助メッセージ(有効期限など)は `info` で別行に出す
- 2026-05-17: トークンを `RegistrationToken` ドメインに集約する構成(独立したサブディレクトリ `RegistrationToken/` 配下)は、`Auth\...\Token\RefreshToken\` 構成と同型にして将来の値オブジェクト追加に備える
- 2026-05-17 (実装中の変更): `phparkitect.php` の `AdminUserComponent::Domain` は `AuthComponent::Domain` への依存を許可していない。また Auth 側の `RandomTokenGenerator` / `TokenHasher` は `Auth\Domain\Services\Token\RefreshToken\*` の interface を実装しているため、AdminUser 側の interface へそのまま bind できない。よって以下に変更：
  - `AdminUser\Domain\Services\RegistrationToken\` 配下に `RandomTokenGeneratorInterface` / `TokenHasherInterface` を新規追加(contract)
  - `AdminUser\Infrastructures\RegistrationToken\` 配下に `RandomTokenGenerator`(`bin2hex(random_bytes(64))`)/ `TokenHasher`(`Hash::make` / `Hash::check`)を新規実装(Auth 側と同等のロジックを duplicate)
  - `AdminUserServiceProvider` で上記の AdminUser interface ↔ AdminUser 実装を bind する。Auth パッケージとの結合は持たない

## Validation

- AC「`admin:invite` 実行で平文トークンが 1 度だけ標準出力に出る」: Feature テスト `InviteCommandTest::outputsPlainTokenOnce` で `assertSuccessful()` と `expectsOutputToContain` / 出力捕捉を用いて検証。`mise run api:test` で実行
- AC「DB にハッシュで 1 行追加される」: Integration テストで `App\Models\AdminUser\RegistrationToken::query()->get()` を取得し、件数・`token` 列が平文と一致しないこと(`Hash::check($plain, $row->token)` が true)を検証
- AC「role / permissions / 未来 expired_at / 未消費 status / email が保存される」: Integration テストで各カラムの値、`permissions` 中間テーブルの行を検証。`status === ConsumptionStatus::Unused->value`、`expired_at > now()`、`email` が CLI 引数と一致
- AC「不正 permission で終了コード 1、行が追加されない」: Feature テストで `assertFailed()` + `App\Models\AdminUser\RegistrationToken::query()->count() === 0` を確認
- AC「既存 `admin:create` の挙動が変わらない」: 既存 `CreateCommandTest` を回帰実行(`mise run api:test`)。本タスクで `CreateCommand` / `CreateUseCase` には触らない
- 共通: `mise run api:ecs` / `mise run api:phpstan` / `mise run api:arkitect` / `mise run api:test` の 4 つを全 green にする
