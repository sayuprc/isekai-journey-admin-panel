# Execution Plan

## Title

worktree を使った並列実装運用の整備

## Status

completed

## Background

ローカル実行系では `git worktree` ごとのポート分離と `.env` 書き戻しがすでに実装されている。一方で、実際の実装作業を並列化するための担当分割、生成物の扱い、検証順、統合手順は明文化されていない。結果として、同時実装時に競合しやすい変更が混ざりやすく、手戻りの原因になる。加えて、worktree 関連ツールの配置が `scripts/` に残っており、実行補助ツールの置き場として一貫していない。

## Goal

`git worktree` を使って複数の実装タスクを安全に並列進行できる運用ルールと実行手順を定義し、着手前に迷わない状態を作る。

## Scope

- 並列化に向くタスク分割単位の定義
- worktree 作成から起動確認までの標準手順
- 競合しやすい領域の先行整理
- 統合順と最小検証セットの定義
- 必要なら関連ドキュメントの更新

## Non-Scope

- CI 全体の刷新
- 新しいブランチ戦略の導入
- `worktree:init` 自体の大きな再設計
- 複数 worktree 間で状態を同期する仕組みの追加

## Acceptance Criteria

- 並列実装の開始手順が 1 つの文書で追える
- 各タスクが「そのまま並列で進められる変更」と「所有者や統合順の明示が必要な変更」に分類されている
- 生成物や `.env` を含む競合注意点が明記されている
- 統合時の順番と確認コマンドが定義されている

## Steps

1. 現状確認
   `tools/worktree/init.sh`、`mise.toml`、`docs/design-docs/local-runtime-topology.md` を基準に、worktree ごとに分離されるものと共有されるものを整理する。
2. 競合面の棚卸し
   並列化の阻害要因を整理する。最低でも次を対象にする。
   - `src/contracts` 起点の変更と生成物更新
   - `mise.toml` / `compose.yaml` / `infra/local/docker/` のような開発基盤変更
   - 同一サブプロジェクト内の近接ファイル集中
   - lockfile や共通設定ファイルの更新
3. タスク分割ルールの定義
   並列実装の基本単位を決める。
   - 原則として Source of Truth 単位で分ける
   - `contracts` を含む変更も専用 ownership を明示すれば並列着手してよい
   - `server` / `admin` / `viewer` の独立タスクは分離可能なら worktree を分ける
   - 生成物を伴う変更は生成責任を 1 worktree に寄せ、他タスクは追従取り込みで統合する
4. 運用フローの確立
   着手から統合までの標準フローを定義する。
   - ベースブランチ更新
   - `git worktree add`
   - `mise run worktree:init`
   - 必要サービスの起動
   - タスク固有の実装と局所検証
   - ベースブランチへの再取り込み
5. 統合ルールの明文化
   競合を減らすための統合順を決める。
   - 依存元になりやすい変更の owner を最初に決める
   - 契約変更と生成物更新は owner worktree で先に確定する
   - 他 worktree はその取り込み後に server / admin / viewer 実装を続ける
   - 基盤変更は専用 worktree に閉じ込め、他 worktree へ早めに再取り込みする
   依存関係が逆転する場合は、その理由と取り込み順を Decision Log に残す。
6. 検証テンプレートの作成
   タスク種別ごとに最低限の確認コマンドを定義する。例:
   - `src/contracts`: `mise run contract:format:check`, `mise run contract:test`
   - `src/server`: `mise run ecs`, `mise run phpstan`, `mise run test`
   - `src/admin`: `cd src && bun --filter admin lint:check`, `build`
   - `src/viewer`: `cd src && bun --filter viewer lint:check`, `build`
7. ドキュメント反映
   実運用に採用するルールを `docs/design-docs/local-runtime-topology.md` へ追記し、入口文書も更新する。

## Decision Log

- 2026-04-26: `git worktree` の隔離機構は既存実装を前提とし、まずは運用ルールの明文化を優先する。
- 2026-04-26: 並列化の単位は「担当者」ではなく「競合しない変更境界」で決める。
- 2026-04-26: `src/contracts` と生成物更新も原則は並列着手可能とし、ownership と再取り込み順で競合を吸収する。
- 2026-04-26: worktree 補助スクリプトは `scripts/` ではなく `tools/worktree/` に寄せる。
- 2026-04-26: 並列実装の運用ルールは `docs/design-docs/local-runtime-topology.md` に集約する。

## Validation

- 2 つ以上の独立タスクをサンプルとして選び、実際に別 worktree で起動できることを確認する
- 並列タスクのうち 1 つに `server`、もう 1 つに `admin` または `viewer` を割り当て、局所検証が衝突しないことを確認する
- `contracts` を含む変更がある場合、owner worktree からの再取り込みで他 worktree が継続できることを確認する
- 2026-04-26: 本体 worktree と追加 2 worktree で `mise run worktree:status` を実行し、割り当てポートの非重複を確認した
- 2026-04-26: 追加 2 worktree で `mise run up` を実行し、証明書同期を含めた `proxy` / `php` / `mysql` / `redis` / `redis-http` の同時起動を確認した
