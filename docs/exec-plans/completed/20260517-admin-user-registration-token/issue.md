# Execution Plan — Issue

## Title

管理画面の招待トークンによるユーザー登録機能

## Background

現状、管理ユーザーの作成は `src/server/app/Console/Commands/AdminUser/CreateCommand.php`（`admin:create` コマンド）でのみ可能で、CLI 実行者が名前・メール・パスワード・権限をすべて入力する必要がある。実際に利用するユーザー本人がパスワードを設定できず、運用上、平文パスワードを CLI 実行者へ共有する必要がある点が問題。

一方で、管理画面 (`src/admin/`) の WebUI から誰でも自由に登録できるようにすると、管理権限の不正取得につながるため許容できない。そのため「CLI でワンタイムの招待トークンを発行 → 受け取った人が WebUI でそのトークンを使って自身の情報を登録する」フローが必要。

## Goal

CLI で発行した招待トークンを使って、対象者本人が管理画面の WebUI から管理ユーザーを登録できるようにする。トークンを持たない者は登録できないことを保証する。

## Scope

- API 契約: `src/contracts/src/admin/admin-users/`（トークン発行は CLI 専用のため契約には不要、登録 API のみ追加。例: `POST /admin-users/registrations` をトークン認証で許可）
- サーバー（CLI）: `src/server/app/Console/Commands/AdminUser/` に招待トークン発行コマンド（例: `admin:invite`）を追加
- サーバー（ドメイン/ユースケース）: `src/server/packages/AdminUser/Domain/Models/` に招待トークンモデル、`Application/Cli/UseCase/` にトークン発行ユースケース、`Application/Admin/UseCase/` にトークン検証＋ユーザー登録ユースケースを追加
- サーバー（永続化）: `src/server/packages/AdminUser/Infrastructures/` に招待トークンリポジトリ実装と DB マイグレーション（`src/server/database/`）を追加。トークンはハッシュ化して保存
- サーバー（ルーティング）: `src/server/packages/AdminUser/Route/AdminUserRouteMap.php` に登録 API を追加
- 管理画面: `src/admin/src/pages/auth/`（または `admin-users/`）に登録ページを追加し、`src/admin/src/components/auth/` にフォーム、`src/admin/src/schemas/` にスキーマを追加。BFF が必要なら `src/admin/src/server/` を追加
- ドキュメント: 運用手順を該当する `docs/` 配下に簡潔に追記

## Non-Scope

- 既存の `admin:create` コマンドの削除や挙動変更
- 招待メール送信機能（トークンの受け渡し手段は CLI 実行者が手動で行う）
- パスワードリセット、メール変更、招待トークンの管理 UI
- 一般ユーザー（viewer 側）の登録フロー
- 役割・権限体系の見直し

## Acceptance Criteria

- `admin:invite`（仮称）CLI コマンドが、引数として役割／権限を受け取り、ワンタイムの招待トークン文字列と有効期限を標準出力に表示する
- 招待トークンは DB にハッシュ化して保存され、平文は発行時の標準出力のみで確認できる
- 管理画面に登録ページが存在し、トークン・名前・メール・パスワードを入力して送信すると管理ユーザーが作成される
- 同じトークンで 2 回目の登録は失敗する（使用済み or 削除されている）
- 有効期限切れ・存在しない・不正なトークンでの登録は API が 401 もしくは 422 を返し、UI にエラーが表示される
- 登録完了後、ユーザーは既存のログイン API（`POST /auth/login`）でログインできる
- TypeSpec の変更は `mise run contract:format:check` / `contract:test` / `contract:compile:admin` が通り、生成物 (`src/server/Generated/`, `src/admin/src/generated/`) が更新済み
- サーバー側は `mise run api:ecs` / `api:phpstan` / `api:arkitect` / `api:test` が通り、招待トークン発行と登録のユースケースに対するテスト（Feature / Unit）が追加されている
- 管理画面は `cd src && bun --filter admin lint:check` / `style:check` / `build` が通る
