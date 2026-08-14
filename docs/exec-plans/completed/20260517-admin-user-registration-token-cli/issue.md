# Execution Plan — Issue

## Title

管理ユーザー登録 WebUI 用の登録トークン発行 CLI

## Background

管理ユーザーは現状 `admin:create` CLI(`src/server/app/Console/Commands/AdminUser/CreateCommand.php`)でメール・パスワード等を指定して直接作成する運用になっており、登録者本人がパスワード等を入力する余地がない

今後、管理者自身が WebUI から登録(パスキー登録を含む)を行えるようにする計画があり、その入口として「管理者が CLI で登録トークンを発行 → 登録対象者へトークンを共有 → 対象者が WebUI 上でトークンを使って自分の管理ユーザーを登録する」という招待型フローを採用したい

本タスクは、このフローの最初のピース(トークンを発行する CLI とその永続化)を用意する

## Goal

管理者が Artisan CLI を叩くと、管理ユーザー登録 WebUI で使用できる単発の登録トークンを発行し、DB に保存したうえで標準出力に提示する。WebUI 側で消費する API は別タスクで扱う

## Scope

- 新規 CLI コマンド
  - `src/server/app/Console/Commands/AdminUser/` 配下に `InviteCommand`(仮名)を追加し、`admin:invite` シグネチャで提供する
  - 既存 `CreateCommand` と同じパターン(`UseCase` 呼び出し + `ResultType` ハンドリング)に従う
- ユースケース
  - `src/server/packages/AdminUser/Application/Cli/UseCase/IssueRegistrationToken/` に `IssueRegistrationTokenUseCase` / `InputData` / `OutputData` を追加
- ドメインモデル
  - `src/server/packages/AdminUser/Domain/Models/` に `RegistrationToken`(値・有効期限・状態)と `RegistrationTokenId`、対応する `RegistrationTokenRepositoryInterface` を追加
  - 役割(`Role`)・権限(`Permission` / `Permissions`)は既存モデルを再利用し、発行時点で付与予定値を保持する
- インフラ
  - `src/server/packages/AdminUser/Infrastructures/` に `RegistrationTokenRepository` を追加
- DB
  - `src/server/database/atlas/schemas/` に `admin-user-registration-tokens.my.hcl` を追加(`refresh-tokens.my.hcl` の形を参考に、トークン値はハッシュ保存、付与予定の role/permissions、`expired_at`、消費状態、`created_at`/`updated_at`)
- 提示形式
  - トークン平文は発行時のみ標準出力に表示し、DB にはハッシュのみ保存する
- テスト
  - `tests/Feature/Console/Commands/AdminUser/` にコマンドの Feature テスト
  - `tests/Unit/AdminUser/Application/Cli/IssueRegistrationToken/` に UseCase の Unit テスト
  - `tests/Integration/AdminUser/Application/Cli/IssueRegistrationToken/` にリポジトリ込みの Integration テスト

## Non-Scope

- 登録 WebUI 本体(Astro/SolidJS 側)と、それが叩く登録 API(`src/contracts` / `src/admin` / `src/server` の HTTP 層)の実装
- パスキー登録ロジックそのものの実装
- トークン失効・再発行・一覧表示 CLI などの管理機能
- 既存 `admin:create` CLI の挙動変更
- メール送信などのトークン配布手段(出力をコピーして手渡しする前提)

## Acceptance Criteria

- `admin:invite <email>` を引数(email 必須、付与予定の role と permissions)付きで実行すると、終了コード 0 で平文トークンが標準出力に 1 度だけ表示される
- 同コマンドの実行ごとに、`admin_user_registration_tokens` テーブルに 1 行が追加され、`token` 列はハッシュ化された値で、平文は保存されていない
- 保存される行には CLI で指定された `email`、付与予定の role / permissions、未来時刻の `expired_at`、未消費を表す `status` が設定されている
- 既存の `admin_users` に同 email が存在する場合は終了コード 1 でエラーメッセージを表示し、テーブルに行が追加されない
- 不正な permission を指定した場合は終了コード 1 でエラーメッセージを表示し、テーブルに行が追加されない
- 既存の `admin:create` コマンドおよび関連テストの挙動が変わらない
- `mise run api:ecs` / `api:phpstan` / `api:arkitect` / `api:test` がすべて成功する
