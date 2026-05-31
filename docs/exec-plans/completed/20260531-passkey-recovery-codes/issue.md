# Execution Plan — Issue

## Title

パスキー紛失時のリカバリーコード発行・管理・復元フロー

## Background

管理ユーザーの認証は passkey に一本化された（`packages/Auth`、`admin_user_passkeys` テーブル、`#781` で管理画面のパスキー認証を追加済み）。登録は招待型の登録トークン（`admin_user_registration_tokens`）を消費して `AdminUser` 作成と passkey credential 登録を同時に行う `RegisterStart` / `RegisterFinish` フローで実現されている（`docs/exec-plans/completed/20260523-passkey-registration-without-password/`）。

しかし passkey はデバイス紐付きの資格情報であり、端末の紛失・故障・初期化やブラウザ/OS の資格情報削除で利用不能になると、その管理ユーザーは二度とログインできない。現状リカバリー手段は CLI `admin:invite` による登録トークンの再発行（実質「別ユーザーとして作り直す」運用）しかなく、本人が自力でアカウントへのアクセスを回復する導線が存在しない。

一般的な解決策である「リカバリーコード（バックアップコード）」を導入する。これは passkey 登録時などにワンタイムのコード群を本人へ提示し、passkey を失った際にコードを 1 つ消費して新しい passkey を再登録する導線を解錠する仕組みである。既存の登録トークン（`RegistrationToken`：平文を一度だけ提示・SHA-256 ハッシュ保存・`ConsumptionStatus` で消費管理・`expired_at` で失効・`TokenHasher` で `hash_equals` 検証）と設計が酷似しており、これに倣って実装できる。

## Goal

passkey を紛失・利用不能にした管理ユーザーが、事前に発行・保管しておいたリカバリーコードを 1 つ消費して本人確認を行い、新しい passkey を再登録してアカウントへのアクセスを回復できるようにする。リカバリーコードはワンタイム・複数発行で、平文は発行時のみ提示しサーバーにはハッシュのみを保存する。発行・再生成・使用は監査ログに記録する。

## Scope

### ドメイン（`src/server/packages/Auth/Domain/`）

リカバリーコードは passkey と同じ `Auth` パッケージの関心事として扱う（passkey 再登録と密結合のため）。`AdminUser` パッケージの `RegistrationToken` 実装を設計の参照元にする。

- `Domain/Models/RecoveryCode/` に新規追加
  - `RecoveryCode`（`recoveryCodeId` / `adminUserId` / `HashedCodeValue` / `ConsumptionStatus`（既存 enum を参照・再利用するか同等を新設）/ `created_at` / `usedAt`）
  - `RecoveryCodeId`、`HashedCodeValue`（`StringValueObject` 継承）
  - `RecoveryCodeRepositoryInterface`（`saveMany` / `findUnusedByAdminUserIdForUpdate` / `deleteByAdminUserId` 等）
- `Domain/Services/RecoveryCode/`
  - `RecoveryCodeIssueService`：N 個（既定 10 個程度）の平文コードを生成し、ハッシュ化した `RecoveryCode` 群と平文一覧を返す（`RegistrationTokenIssueService::issue` に倣う）
  - `RecoveryCodeVerifyService`：平文コードと `adminUserId`（または email）から未使用・有効なコードを `hash_equals` で照合し、当該コードを消費する（`RegistrationTokenConsumeService::verify` に倣う）
  - `RandomRecoveryCodeGeneratorInterface` / `RecoveryCodeHasherInterface`（既存 `RandomTokenGeneratorInterface` / `TokenHasherInterface` 相当。人が転記しやすい形式を検討）

### インフラ・DB（`src/server/packages/Auth/Infrastructures/`, `src/server/database/atlas/schemas/`）

- `Infrastructures/RecoveryCode/` に `RecoveryCodeRepository` / `RandomRecoveryCodeGenerator` / `RecoveryCodeHasher`（SHA-256・`hash_equals`、`TokenHasher` 同等）
- `src/server/database/atlas/schemas/admin-user-recovery-codes.my.hcl` を新規追加（`admin-user-registration-tokens.my.hcl` / `admin-user-passkeys.my.hcl` を参考）。列: `admin_user_recovery_code_id`(binary16, PK) / `admin_user_id`(binary16, FK→`admin_users`, on_delete CASCADE) / `code`(ハッシュ) / `status`(消費状態) / `used_at`(nullable) / `created_at` / `updated_at`。`admin_user_id` にインデックス
- 対応する Eloquent モデル `src/server/app/Models/AdminUser/`（または `Auth/`）に追加

### アプリケーション（`src/server/packages/Auth/Application/Admin/UseCase/`）

- 発行・再生成（認証済みユーザー向け）【確定】
  - `RecoveryCode/Generate`（仮）UseCase：認証済み管理ユーザーの既存リカバリーコードを全削除し新規発行、平文一覧を返す。監査ログに記録。**ログイン後の発行用画面から手動で発行/再生成する方式とし、`RegisterFinishUseCase` 完了フローへは組み込まない**（passkey 登録時の自動発行はしない）
- 復元フロー（未認証ユーザー向け、login と同列の入口）
  - `RecoveryStart`（仮）：email + リカバリーコードを検証し、コードを消費したうえで passkey 登録 ceremony を開始する。**`PasskeyCeremonyType` に `Recovery` 種別を新設する**（Register 再利用はしない）
  - `RecoveryFinish`（仮）：WebAuthn credential を検証し新しい passkey を `admin_user_passkeys` に保存、refresh/access トークンを発行してログイン状態にする（`RegisterFinishUseCase::persist` の passkey 保存部を参照）。**既存 passkey は削除せず、新しい passkey を追加する**
  - ユーザー列挙対策は `LoginStartUseCase` の方針（実在/非実在で応答形状を変えない）に倣う

### コントラクト（`src/contracts/src/admin/auth/`）

- `domain.tsp`：`recoveryCode`（`@secret` 平文スカラ）を追加
- `transport.tsp`：`RecoveryStartRequest/Response`、`RecoveryFinishRequest/Response`、（認証済み発行 API を設ける場合）`GenerateRecoveryCodesResponse`（平文コード一覧）を追加
- `service.tsp`：`/auth/recovery/start`・`/auth/recovery/finish`、必要なら認証済みの発行/再生成エンドポイントを `AuthenticateService` または別 interface に追加
- `mise run generate` で `src/contracts/generated/oas/`・`src/server/Generated/`・`src/admin/src/generated/` を再生成

### サーバー HTTP / ルート

- `src/server/app/Http/Controllers/Api/Admin/V1/Auth/` に `RecoveryStartController` / `RecoveryFinishController`（必要なら発行用 Controller）を追加
- `src/server/routes/admin.php` の auth group に route 追加。start 系には login start と同様の `throttle:` を付与
- `src/server/packages/Auth/Route/AuthRouteMap.php` に route name を追加

### 監査ログ（`src/server/packages/Support/UseCase/AuditLog/`）

- `AuditAction` に `RecoveryCodeIssue`（発行/再生成）と `RecoveryCodeUse`（復元による消費）相当を追加（命名は Plan で確定）。`AuditTargetType::AdminUser` を流用
- 発行・再生成・復元成功時に `AuditLogRecorderInterface::record` で記録（`LoginFinishUseCase` / `RegisterFinishUseCase` の記録に倣う）

### 管理画面フロント（`src/admin/`）

- 復元入口ページ/コンポーネント：`src/admin/src/pages/auth/recovery.astro` と `src/admin/src/components/auth/RecoveryForm.tsx`（`login.astro` / `LoginForm.tsx` 参照）。email + リカバリーコード入力 → WebAuthn 登録 ceremony 実行 → 新 passkey 登録
- BFF：`src/admin/src/server/routes/auth.ts` に recovery start/finish の中継を追加。WebAuthn ceremony は `src/admin/src/utils/webauthn.ts` を再利用
- リカバリーコード提示 UI：発行/再生成 API を設ける場合、平文コード一覧を一度だけ表示しコピー/ダウンロードできる画面。ログイン画面からリカバリー導線へのリンク

### CLI（任意・Plan で要否判断）

- 既存 `admin:invite`（`InviteCommand`）に倣い、管理者がユーザーのリカバリーコードを強制再発行する CLI を設けるかは Plan で判断する

### テスト

- Unit: `RecoveryCodeIssueService` / `RecoveryCodeVerifyService` / `RecoveryCodeHasher`（`tests/Unit/Auth/...`、`php-unit-test-creator`）
- Integration: `RecoveryCodeRepository`、消費サービス（`tests/Integration/Auth/...`、`php-integration-test-creator`）
- Feature: recovery start/finish・発行 API（`tests/Feature/Api/Admin/V1/Auth/`、`php-feature-test-creator`）
- 管理画面: BFF route のテスト（`src/admin/src/server/routes/auth.test.ts` に倣う）、`lint:check` / `style:check` / `build`

## Non-Scope

- email/SMS によるリカバリーコードや復元リンクの自動配信（提示はその場の画面表示・手動保管前提）
- TOTP/SMS など passkey 以外の第 2 要素の追加
- 既存の login / register / refresh フローの仕様変更（発行タイミングで `RegisterFinish` に組み込む場合の追記を除く）
- 既存データの移行戦略（本番ユーザー不在のため破壊的なスキーマ変更を許容）
- リカバリーコードの残数通知やローテーション催促などの運用 UX
- viewer（`src/viewer/`）側の変更

## Acceptance Criteria

- リカバリーコード発行を実行すると、人が転記可能な複数（既定数）の平文コードがその場で一度だけ提示され、DB の `admin_user_recovery_codes` には各コードのハッシュのみが保存される（平文は保存されない）
- 再生成を実行すると当該ユーザーの既存リカバリーコードは無効化（削除または消費扱い）され、新しいコード群に置き換わる
- passkey を持たない/失った管理ユーザーが、email と有効な未使用リカバリーコードを 1 つ提示して復元フローを完了すると、新しい passkey が `admin_user_passkeys` に登録され、ログイン済み状態（access/refresh トークン発行）になる
- 復元に使用したリカバリーコードは消費済みとなり、同じコードでの二度目の復元は失敗する
- 無効・使用済み・存在しないリカバリーコード、または email とコードの組み合わせ不一致では復元が失敗し、`LoginStart` と同様にユーザー列挙が成立しない（実在/非実在で応答が区別できない）
- リカバリーコードの発行・再生成・復元成功が監査ログ（`audit_logs`）に記録される
- recovery start エンドポイントに login start と同等の throttle が適用されている
- API 契約変更は `src/contracts` を起点に行い、生成物（`generated/`）は手動編集せず再生成で更新されている
- `mise run api:ecs` / `api:phpstan` / `api:arkitect` / `api:test`、`mise run contract:format:check` / `contract:test`、`cd src && bun --filter admin lint:check` / `style:check` / `build` がすべて成功する
