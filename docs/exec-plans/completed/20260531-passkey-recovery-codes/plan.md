# Execution Plan — Plan

実装計画(どう進めるか)。問題定義は同ディレクトリの `issue.md` を参照する

## Title

パスキー紛失時のリカバリーコード発行・管理・復元フロー

## Status

completed

## Steps

各ステップは「変更ファイル / 追加・修正・削除内容 / 倣う既存実装 / 依存する前ステップ」を明記する
契約変更(Step 5)を含むため、生成物(`src/contracts/generated/`・`src/server/Generated/`・`src/admin/src/generated/`)は手動編集せず再生成タスクで更新する

### Step 1: DB スキーマ + Eloquent モデル ✅

依存: なし

- 追加 `src/server/database/atlas/schemas/admin-user-recovery-codes.my.hcl`
  - `admin-user-passkeys.my.hcl` / `admin-user-registration-tokens.my.hcl` に倣う
  - 列: `admin_user_recovery_code_id`(binary16, PK) / `admin_user_id`(binary16) / `code`(varchar(255), ハッシュ) / `status`(tinyint unsigned, 消費状態) / `used_at`(datetime, null) / `created_at`(datetime) / `updated_at`(datetime)
  - index `admin_user_recovery_codes_admin_user_id_index`(`columns = [column.admin_user_id]`)
  - foreign_key `fk_admin_user_recovery_codes_admin_user_id` → `table.admin_users.column.admin_user_id`、`on_delete = CASCADE`(`admin-user-passkeys.my.hcl` と同じ)
- 追加 `src/server/app/Models/AdminUser/RecoveryCode.php`
  - `App\Models\AdminUser\RegistrationToken`(`$table` / `$primaryKey` / `$keyType='string'` / `casts` で `used_at`・`created_at`・`updated_at` を `immutable_datetime`)に倣う。permission のような子テーブルは無いので `$with` リレーションは不要
- スキーマ反映は `mise run migrate:local` / `mise run migrate:testing`(内部で atlas apply)。Step 9 のテスト前に testing DB を反映する

### Step 2: ドメイン層(Auth/Domain/Models/RecoveryCode)✅

依存: Step 1(テーブル定義の列に整合させる。コードは DB に依存しない)

`Auth\Domain\Models\RecoveryCode\` を新規追加。Arkitect 上 `AuthComponent::Domain` は `AdminUserComponent::Domain`(`AdminUserId` / `Email`)・`SupportComponent::Domain`(`StringValueObject` / `UuidValueObject` / `DomainError`)・`ResultType` に依存可能(`tools/Arkitect/config.php` で確認済み)。RegistrationToken のモデル群に倣う

- `RecoveryCodeId.php`: `Support\Domain\ValueObjects\String\UuidValueObject` を継承(`RegistrationTokenId` に倣う)
- `HashedCodeValue.php`: `StringValueObject` を継承(`HashedTokenValue` に倣う)
- `ConsumptionStatus.php`: `enum: int { Unused=0; Consumed=1; }` + `isAvailable()`(`RegistrationToken\ConsumptionStatus` をそのままコピー。Auth パッケージ内に独立して新設し AdminUser 側 enum を再利用しない＝パッケージ越境を避ける)
- `RecoveryCode.php`(`readonly`): プロパティ `RecoveryCodeId $recoveryCodeId` / `AdminUserId $adminUserId` / `HashedCodeValue $code` / `ConsumptionStatus $status` / `?DateTimeImmutable $usedAt`。`reconstruct()` / `toArray()`(`admin_user_recovery_code_id`/`admin_user_id`/`code`/`status`/`used_at`)/ `isAvailable()` / `consume(DateTimeImmutable $now)`(status を `Consumed`、`usedAt` を `$now` に)を `RegistrationToken` に倣って実装。`created_at` は永続化時に `now()` を入れる方針(RegistrationTokenRepository と同様)
- `RecoveryCodeRepositoryInterface.php`: `saveMany(list<RecoveryCode> $codes): void` / `findUnusedByAdminUserIdForUpdate(AdminUserId $adminUserId): list<RecoveryCode>` / `save(RecoveryCode $code): RecoveryCode`(消費した 1 件の更新用)/ `deleteByAdminUserId(AdminUserId $adminUserId): void`(再生成時の全削除用)

### Step 3: ドメインサービス(Auth/Domain/Services/RecoveryCode)✅

依存: Step 2

`Auth\Domain\Services\RecoveryCode\` を新規追加。RegistrationToken のサービス群に倣う

- `RandomRecoveryCodeGeneratorInterface.php`: `generate(): string`(`RegistrationToken\RandomTokenGeneratorInterface` に倣う)
- `RecoveryCodeHasherInterface.php`: `hash(string $plain): string` / `verify(string $plain, string $hashed): bool`(`RegistrationToken\TokenHasherInterface` に倣う)
- `RecoveryCodeIssueService.php`: `issue(AdminUserId $adminUserId): Result<array{codes: list<RecoveryCode>, plainCodes: list<string>}, DomainError>`。`CODE_COUNT = 10`(定数)回ループし、各回で generator→hasher→`HashedCodeValue::create` で `RecoveryCode`(status=Unused, usedAt=null) を生成。`RegistrationTokenIssueService::issue` のエラー集約パターン(`EntityRuleViolationError` を `DomainValidationError` に畳む)に倣う
- `RecoveryCodeVerifyService.php`: `verify(#[SensitiveParameter] string $plainCode, AdminUserId $adminUserId): Result<RecoveryCode, DomainError>`。`repository->findUnusedByAdminUserIdForUpdate` を回し `hasher->verify` で一致探索、`isAvailable($clock->now())` を確認。`RegistrationTokenConsumeService::verify` に倣い、不一致・無効はすべて同一の `BusinessRuleViolationError('recovery_code_not_found')` を返す(タイミング・応答を区別させない)

### Step 4: インフラ層(Auth/Infrastructures/RecoveryCode)+ DI バインド ✅

依存: Step 1, Step 2, Step 3

- 追加 `src/server/packages/Auth/Infrastructures/RecoveryCode/RecoveryCodeHasher.php`: `hash('sha256', ...)` + `hash_equals`(`AdminUser\Infrastructures\RegistrationToken\TokenHasher` をコピー)
- 追加 `.../RandomRecoveryCodeGenerator.php`: 人が転記しやすい形式。`base32` 風(`ABCDEFGHJKLMNPQRSTUVWXYZ23456789` から `random_int` で 10 文字、4-4 でハイフン区切り 例 `A3KP-9QXR`)を生成する。実装は `random_int` ループ(`RandomTokenGenerator` の `random_bytes` に対し、可読性のため char set 方式にする)
- 追加 `.../RecoveryCodeRepository.php`: `App\Models\AdminUser\RecoveryCode` を使い、`saveMany`(複数 insert)/ `findUnusedByAdminUserIdForUpdate`(`where status=Unused`・`lockForUpdate`・`UuidConverterInterface` で bin↔uuid 変換)/ `save`(消費 1 件 upsert で `status`・`used_at` 更新)/ `deleteByAdminUserId`(`where admin_user_id`→delete)を実装。`AdminUser\Infrastructures\RegistrationToken\RegistrationTokenRepository`(`UuidConverterInterface` 利用・`hydrate` パターン)に倣う
- `src/server/app/Providers/Domain/AuthServiceProvider.php` に `bind`:
  `RecoveryCodeRepositoryInterface→RecoveryCodeRepository` / `RandomRecoveryCodeGeneratorInterface→RandomRecoveryCodeGenerator` / `RecoveryCodeHasherInterface→RecoveryCodeHasher`

### Step 5: 契約(TypeSpec)+ 再生成 ✅

依存: なし(実装より先でも可。Step 6 以降は生成物に依存)

- `src/contracts/src/admin/auth/domain.tsp`: `@secret scalar recoveryCode extends string;`(`registrationToken` に倣う)を追加
- `src/contracts/src/admin/auth/transport.tsp`:
  - `RecoveryStartRequest { email: email; recoveryCode: recoveryCode; name: adminUserName; }`(新 passkey 名のため `name` を含める。`RegisterStartRequest` に倣う)
  - `RecoveryStartResponse { authCeremonyId; publicKey: webAuthnPublicKeyOptions; }`
  - `RecoveryFinishRequest { authCeremonyId; credential: webAuthnCredential; }`(finish 時はコード再検証不要。ceremony が消費を保持)
  - `RecoveryFinishResponse { accessToken; refreshTokenId; refreshToken; }`(`LoginFinishResponse` 同形)
  - `GenerateRecoveryCodesResponse { recoveryCodes: recoveryCode[]; }`(発行/再生成の平文一覧)
- `src/contracts/src/admin/auth/service.tsp`:
  - `AuthenticateService`(未認証 group)に `recoveryStart`(`/auth/recovery/start`) と `recoveryFinish`(`/auth/recovery/finish`) を `loginStart`/`loginFinish` と同じ戻り union で追加
  - 認証済み発行/再生成は新 interface(`@route("/recovery-codes")` の `RecoveryCodeService` 等)に `generate`(POST `/recovery-codes`) を `GenerateRecoveryCodesResponse | Unauthorized | ServerError` で追加。`main.tsp` から取り込まれているか確認し、必要なら import 追記(`src/contracts/src/admin/main.tsp` 確認)
- 再生成: `mise run generate`(`api:generate` + `admin:generate` + `viewer:generate`)。生成物は手動編集しない。検証は `mise run contract:format:check` / `contract:test`

### Step 6: アプリケーション UseCase(Auth/Application/Admin/UseCase)✅

依存: Step 2, 3, 4, 5

`PasskeyCeremonyType` に `case Recovery = 'recovery';` を追加(`src/server/packages/Auth/Domain/Models/PasskeyCeremonyType.php`)

- `RecoveryCode/Generate/`(認証済み)
  - `GenerateRecoveryCodesUseCase`: 現在の認証ユーザーを `Auth\Domain\Models\AuthContext::get()` から取得(`Authenticate` ミドルウェア→`AuthenticateUseCase` が `AuthContext` に user をセット済み。`AuthContext` は `scoped` バインド)。`TransactionInterface::scope` 内で `repository->deleteByAdminUserId` → `RecoveryCodeIssueService::issue` → `repository->saveMany` を実行し、監査ログ(Step 8)に記録、平文一覧を `GenerateRecoveryCodesOutputData` で返す。AuthContext が null の場合は `AuthenticationError`
  - `GenerateRecoveryCodesOutputData { list<string> $plainCodes }`
- `Recovery/RecoveryStartUseCase`(未認証)
  - 入力 `RecoveryStartInputData { email, plainCode, name }`。`Email::create` → `AdminUserRepository->findByEmail`。ユーザー列挙対策として `LoginStartUseCase` に倣い、**実在/非実在・コード正否に依らず常に passkey 登録 ceremony を開始して同形状を返す**。実在ユーザーかつ `RecoveryCodeVerifyService::verify` 成功時のみ、消費した RecoveryCode を `repository->save($code->consume(now))` し、ceremony state に本物の `adminUserId` を束縛。それ以外はダミー `adminUserId`(`uuidGenerator->generate()`)を束縛し finish 時に必ず失敗させる
  - `passkeyAuthenticator->startRegistration(userHandle, email, name)`(`RegisterStartUseCase` に倣う。`userHandle` は `PasskeyUserHandleGeneratorInterface->generate()`)。`ceremonyStore->put(new PasskeyCeremonyState(authCeremonyId, PasskeyCeremonyType::Recovery, email, name, adminUserId, optionsJson))`
  - 注意: コード消費を start 側で行うか finish 側で行うかは要確定。**finish が失敗するとコードが無駄になる問題**を避けるため、消費は finish 成功時(Step 6 RecoveryFinish の transaction 内)に行う設計とする。start では「検証のみ」行い ceremony state に検証成功フラグ相当(本物 adminUserId 束縛)を持たせ、消費は finish で `verify`→`save(consume)` する。→ Decision Log 参照
  - 出力 `RecoveryStartOutputData { authCeremonyId, publicKey }`
- `Recovery/RecoveryFinishUseCase`(未認証)
  - 入力 `RecoveryFinishInputData { authCeremonyId, credential }`。`ceremonyStore->pull` し `type === Recovery` を確認(不一致は `AuthenticationError`／`LoginFinishUseCase` に倣う)。`passkeyAuthenticator->finishRegistration(credential, optionsJson)` で credential 検証
  - `TransactionInterface::scope` 内で:
    - state の email から RecoveryCode を再 `verify`→`save(consume)`(消費はここで確定)。失敗時 `AuthenticationError`
    - `passkeyRepository->save(new AdminUserPasskey(...))` で**新しい passkey を追加(既存は削除しない)**。`name` は state->name。`RegisterFinishUseCase::persist` の passkey 保存部に倣う
    - `refreshTokenIssueService->issue` → `accessTokenIssueService->issue` → `refreshTokenRepository->save`(`LoginFinishUseCase::persist` に倣う)
    - 監査ログ記録(Step 8)
  - 出力 `RecoveryFinishOutputData { accessToken, refreshTokenId, refreshToken }`
  - DI: 上記 UseCase は constructor injection。AuthServiceProvider への追加バインドは不要(具象は autowiring、interface は Step 4 で登録済み)

### Step 7: サーバー HTTP(Controller / Presenter / Route)✅

依存: Step 5, Step 6

- 追加 Controller `src/server/app/Http/Controllers/Api/Admin/V1/Auth/`:
  - `RecoveryStartController`(`RegisterStartController` に倣い `RecoveryStartInputData` を組み立て presenter へ)
  - `RecoveryFinishController`(`LoginFinishController` に倣う)
  - `GenerateRecoveryCodesController`(認証済み。body 不要、UseCase を呼ぶだけ)
- 追加 Presenter `src/server/app/Http/Presenters/Api/Admin/V1/Auth/`:
  - `RecoveryStartPresenter`(`RegisterStartPresenter` に倣う・`RecoveryStartResponse`)
  - `RecoveryFinishPresenter`(`LoginFinishPresenter` に倣う・`RecoveryFinishResponse`)
  - `GenerateRecoveryCodesPresenter`(`GenerateRecoveryCodesResponse`・`setRecoveryCodes`)
- `src/server/packages/Auth/Route/AuthRouteMap.php` に `RecoveryStart='recovery.start'` / `RecoveryFinish='recovery.finish'` / `GenerateRecoveryCodes='recovery-codes.generate'` を追加
- `src/server/routes/admin.php`:
  - auth group に `POST /recovery/start`(`middleware('throttle:passkey-recovery-start')`)と `POST /recovery/finish`(throttle 無し＝ finish は ceremony 単回消費のため。`register/finish` に倣う)
  - `Authenticate::class` group に `POST /recovery-codes`(`GenerateRecoveryCodesController`)
- `src/server/app/Providers/AppServiceProvider.php` の `registerPasskeyRateLimiters()` に `passkey-recovery-start` を `passkey-login-start` と同形で追加(`emailKey` でキー化)。`config/auth.php` の `passkey.rate_limit` に `recovery` の既定値を追加(`login` に倣う)

### Step 8: 監査ログ ✅

依存: Step 6

- `src/server/packages/Support/UseCase/AuditLog/AuditAction.php` に `RecoveryCodeIssue='recovery_code_issue'`(発行/再生成)と `RecoveryCodeUse='recovery_code_use'`(復元消費)を追加
- Generate UseCase 成功時に `recorder->record(AuditAction::RecoveryCodeIssue, AuditTargetType::AdminUser, $adminUserId, ['count'=>...], $adminUserId)`
- RecoveryFinish UseCase 成功時に `recorder->record(AuditAction::RecoveryCodeUse, AuditTargetType::AdminUser, $adminUserId, ['admin_user_passkey_id'=>..., 'refresh_token_id'=>...], $adminUserId)`
- 平文コードはスナップショットに含めない(`AuditLogRecorderInterface` の機密値除外方針)。`AuditTargetType::AdminUser` を流用

### Step 9: フロント(発行画面・復元画面・BFF)✅

依存: Step 5(生成物), Step 7

- BFF `src/admin/src/server/routes/auth.ts`:
  - `/recovery/start`(`register/start` に倣う。`recoveryStart` 生成クライアント呼び出し、`enforceAuthRateLimit` を追加。`AUTH_RATE_LIMITS` に `recoveryStart` を `src/admin/src/server/constants.ts` で追加)
  - `/recovery/finish`(`login/finish` に倣う。成功時にセッション/CSRF cookie を発行)
  - 発行/再生成 `/recovery-codes`(認証必須 BFF。既存の認証付き BFF route の認証ヘッダ付与方式に倣う。`recoveryCodes` 配列を返却)
- 復元画面 `src/admin/src/pages/auth/recovery.astro` + `src/admin/src/components/auth/RecoveryForm.tsx`(`login.astro`/`LoginForm.tsx`・`register.astro`/`RegisterForm.tsx` に倣う)。email + リカバリーコード + passkey 名 → `/auth/recovery/start` → `src/admin/src/utils/webauthn.ts` の登録 ceremony 実行 → `/auth/recovery/finish`。`login.astro` から recovery 画面へのリンクを追加
- 発行/提示 UI: 認証後にアクセスできる画面(既存の認証済みページ構成に倣う)から `/recovery-codes` を呼び、返却された平文コード一覧を一度だけ表示しコピー/ダウンロードできる UI。再表示できない旨の注意書きを表示
- `src/viewer/` は変更しない(Non-Scope)

### Step 10: テスト + 最終検証 ✅

依存: Step 1–9

- Unit(`php-unit-test-creator`, `tests/Unit/Auth/...`): `RecoveryCodeIssueService`(生成数・平文/ハッシュ整合)/ `RecoveryCodeVerifyService`(一致・不一致・使用済み・期限/無効が同一エラー)/ `RecoveryCodeHasher`(hash/verify)
- Integration(`php-integration-test-creator`, `tests/Integration/Auth/...`, `DatabaseTestCase`): `RecoveryCodeRepository`(saveMany/findUnused.../save(consume)/deleteByAdminUserId)
- Feature(`php-feature-test-creator`, `tests/Feature/Api/Admin/V1/Auth/`, `DatabaseTestCase`): 発行 API(認証必須・ハッシュのみ保存・平文返却)/ 再生成(旧コード消失)/ recovery start→finish(新 passkey 追加・トークン発行)/ 同コード二度目失敗 / 不正 email・コードでユーザー列挙不成立(応答同形)/ 監査ログ記録 / throttle 適用
- 管理画面: `src/admin/src/server/routes/auth.test.ts` に recovery start/finish/generate の BFF テストを追加(既存に倣う)

## Decision Log

- 2026-05-31: 発行方式はログイン後の発行用画面からの手動発行/再生成とする(認証済み `GenerateRecoveryCodesUseCase` + `POST /recovery-codes` + 管理画面)。`RegisterFinishUseCase` への自動発行組み込みと CLI 発行は今回作らない(ユーザー確定方針 1)
- 2026-05-31: 復元 ceremony は `PasskeyCeremonyType` に `Recovery` 種別を新設する。既存 `Register` の再利用はしない(ユーザー確定方針 2。finish 側で `type === Recovery` を厳格に判定するため)
- 2026-05-31: 復元成功時、既存 passkey は削除せず新しい passkey を `admin_user_passkeys` に追加する(ユーザー確定方針 3。`RegisterFinishUseCase::persist` の保存部に倣う)
- 2026-05-31: RecoveryCode は `Auth` パッケージ(`Auth\Domain\Models\RecoveryCode`)に置く。Arkitect 上 `AuthComponent::Domain` は `AdminUserComponent::Domain` に依存可(`tools/Arkitect/config.php`)なので `AdminUserId`/`Email` 参照は越境にならない。passkey 再登録と密結合のため AdminUser ではなく Auth が妥当
- 2026-05-31: `ConsumptionStatus` は AdminUser 側 enum を再利用せず Auth パッケージ内に同等を新設する。逆依存(AdminUser→Auth は UseCase のみ許可、Domain 間の AdminUser←Auth 参照は不可方向)を避け、Auth ドメインを自己完結させるため
- 2026-05-31: リカバリーコードの消費は `RecoveryStart` ではなく `RecoveryFinish` 成功時(transaction 内)に確定する。start で消費すると WebAuthn ceremony 失敗時にコードが無駄になるため。start では検証のみ行い、検証成功時だけ ceremony state に本物の `adminUserId` を束縛し、finish で再 `verify`→`consume` する
- 2026-05-31: ユーザー列挙対策は `LoginStartUseCase` 方針に倣う。`RecoveryStart` は実在/非実在・コード正否に依らず常に登録 ceremony を返し、応答形状・タイミングを区別させない。`RecoveryCodeVerifyService` も不一致/無効をすべて同一エラーで返す
- 2026-05-31: リカバリーコード形式は可読性優先で `ABCDEFGHJKLMNPQRSTUVWXYZ23456789`(紛らわしい 0/O/1/I/L を除外)から `random_int` で生成し 4-4 ハイフン区切りとする。既定発行数は 10
- 2026-05-31: DB は Atlas HCL(`src/server/database/atlas/schemas/*.my.hcl`)方式。新規テーブルは `admin-user-recovery-codes.my.hcl` を追加し `mise run migrate:local` / `migrate:testing` で反映する。FK は `admin_users` に CASCADE
- 2026-05-31: 契約は `src/contracts` の TypeSpec を起点に変更し、`mise run generate` で全生成物を更新する。生成物は手動編集しない(ARCHITECTURE.md / contracts 規約)
- 2026-05-31: Atlas のスキーマは自動探索ではなく `src/server/database/atlas/atlas.hcl` の `table_schemas` に明示列挙されている。新規 `admin-user-recovery-codes.my.hcl` を同リストへ追加して反映した(plan に明記が無かったため追記)
- 2026-05-31: `mise run migrate:local` / `migrate:testing` は対話承認待ちになり非対話実行で失敗するため、env を読み込んだうえで `atlas schema apply --auto-approve` を直接実行して local/testing 双方へ反映した
- 2026-05-31: `mise run generate` は既存 OAS を入力にコード生成するだけで TypeSpec→OAS の再コンパイルを含まない。契約変更後は先に `mise run contract:compile:admin`(または `contract:re-compile`)で OAS を更新してから `mise run generate` を実行する必要がある
- 2026-05-31: 管理画面 BFF テストは `auth.test.ts` に recovery start/finish を追加した(既存パターンに準拠、4 ケース追加し計 9 件 green)。認証必須の `/recovery-codes` 生成 BFF はテストを追加しなかった: リポジトリに authGuard 付き BFF ルートのテスト前例が無く(`admin-users.ts` も未テスト)、ルート自体は `admin-users.ts` と同形の薄い透過実装のため。新規のテストハーネス導入は最小変更原則に反すると判断
- 2026-05-31: `audit_logs.action` 列が `varchar(16)` で、新規 `recovery_code_issue`(19文字)/`recovery_code_use`(17文字) が桁あふれ(1406 Data too long)した。Scope の監査ログ範囲として `audit-logs.my.hcl` の `action` を `varchar(64)`(`target_type` と同幅)へ拡張し local/testing に反映した
- 2026-05-31: `cd src && bun --filter admin build` はユーザー判断によりスキップ(既存事情で失敗するため不要との指示)。`lint:check` / `style:check` は実行して成功を確認した
- 2026-05-31: `RecoveryFinishRequest` には平文コードを含めない(plan の契約定義どおり)ため、finish 側で「再 verify」は平文照合では行えない。代わりに次の設計とした: start で検証成功した場合のみ ceremony state に**本物の adminUserId**を束縛する。finish では `transaction` 内で `AdminUserRepository::find(state->adminUserId)` を行い、(1) 実在ユーザーであること(ダミー id は null で失敗)と (2) `findUnusedByAdminUserIdForUpdate` で未使用コードが残っていることを確認し、その 1 件を `save(consume)` で消費する。これにより「消費は finish 確定・ceremony 失敗時は無駄にならない・同一コードの二度使い不可・ユーザー列挙不成立」を満たす。平文コードを finish まで持ち回らない設計のため、消費対象は未使用コードのうち 1 件(順序は repository の `orderByDesc(created_at)`)とする
- 2026-05-31: 【レビュー指摘修正】上記「未使用コードのうち先頭 1 件を消費」する設計は、複数コード発行時に「入力したコード」と「消費されるコード」が無関係になり、同一コードを未使用コードが尽きるまで使い回せる欠陥があった。修正として、start で `RecoveryCodeVerifyService::verify` が特定した `RecoveryCode` の id を ceremony state の `recoveryCodeId`(nullable・cache 永続化対象)に束縛する。finish では `RecoveryCodeRepository::findUnusedByIdForUpdate(recoveryCodeId, adminUserId)`(新設・`lockForUpdate`)でその id の未使用コードのみを取得して `consume`→`save` する。state に `recoveryCodeId` が無い(非実在/コード不正時は null 束縛)/該当コードが使用済み or 存在しない場合は `AuthenticationError`。これにより「消費されるのは入力・検証した当該コードのみ」「同一コードの二度使い不可」「A 以外の未使用コードは過剰消費されない」を保証する。消費は finish 確定・ceremony 失敗時は無駄にならない方針は維持
- 2026-05-31: リカバリーコード長は `RandomRecoveryCodeGenerator` で `ABCDEFGHJKLMNPQRSTUVWXYZ23456789`(32 文字集合)から 8 文字(4-4 ハイフン区切り)生成し、エントロピーは log2(32^8) = 40bit。これはオフラインのパスワードハッシュ用途には不足だが、本機能は (1) ワンタイム(使用後即消費)、(2) recovery start に `throttle:passkey-recovery-start`(email 単位レート制限)、(3) 既定 10 個発行(総当たり対象は最大 10 個 × 40bit)、(4) ハッシュ保存(DB 漏洩時も平文露出しない)という多層防御下にあり、オンライン総当たりは throttle で実質的に阻止される。よって 8 文字(40bit)を許容と判断し現状維持とする。将来 throttle を緩める/オフライン耐性を要件化する場合は GROUP_LENGTH/GROUP_COUNT を増やし、Feature テスト(固定コード文字列)と契約上の長さ前提を併せて見直す

## Validation

各 Acceptance Criteria に対応する検証手順(実在する `mise` タスク名で記載)

- 発行で複数平文コードがその場で一度だけ提示・DB はハッシュのみ:
  Feature テスト(発行 API レスポンスに平文配列、`admin_user_recovery_codes.code` が平文と不一致＝ハッシュ)+ Unit(`RecoveryCodeIssueService`)。`mise run api:test`
- 再生成で旧コードが無効化され置換:
  Feature テスト(2 回目発行後に 1 回目コードで復元が失敗、`deleteByAdminUserId` 後の件数)。`mise run api:test`
- passkey 喪失ユーザーが email + 未使用コードで復元 → 新 passkey 登録・ログイン状態:
  Feature テスト(recovery start→finish で `admin_user_passkeys` に行追加・既存行は残存・access/refresh トークン返却)。`mise run api:test`
- 使用済みコードでの二度目復元が失敗:
  Feature テスト(同一コードで再度 start→finish が `AuthenticationError`)。`mise run api:test`
- 無効/使用済み/存在しないコード・email 不一致でユーザー列挙不成立:
  Feature テスト(実在/非実在・正否でレスポンス形状・ステータスが同一)+ Unit(`RecoveryCodeVerifyService` が同一エラー)。`mise run api:test`
- 発行・再生成・復元成功が監査ログに記録:
  Feature テスト(`audit_logs` に `recovery_code_issue` / `recovery_code_use` 行)。`mise run api:test`
- recovery start に login start 同等の throttle:
  Feature テスト(`throttle:passkey-recovery-start` ミドルウェア適用・上限超過で 429)。`mise run api:test`
- 契約変更は `src/contracts` 起点・生成物は再生成のみ:
  `mise run generate` 後に diff を確認し `generated/` への手動編集が無いこと。`mise run contract:format:check` / `mise run contract:test`
- 静的解析・全テスト・フロントビルド:
  `mise run api:ecs` / `mise run api:phpstan` / `mise run api:arkitect` / `mise run api:test`、
  `mise run contract:format:check` / `mise run contract:test`、
  `cd src && bun --filter admin lint:check` / `cd src && bun --filter admin style:check` / `cd src && bun --filter admin build`
- 復元画面の手動確認:
  `mise run admin:dev` で `recovery.astro` を開き、email + コード + passkey 名で復元 ceremony が完走しダッシュボードに遷移すること
