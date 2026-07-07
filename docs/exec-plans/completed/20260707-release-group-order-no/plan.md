# Plan — ReleaseGroup への表示順追加

## Title

release_groups に order_no を追加し、グループ自体の表示順を制御可能にする

## Status

completed

## Background

これまでリリースグループの並びは「傘下の公開リリースの最古発売日 (first_released_on) の降順」という導出値のみで決まっており、グループ自体に表示順を持たせる手段がなかった。1 階層下の Release には既に order_no があるため、同じパターンを ReleaseGroup にも展開する

## Goal

admin でリリースグループの表示順 (orderNo) を制御でき、viewer / admin の一覧が order_no を最優先に並ぶようにする。新規作成時は入力させず「既存の最大 order_no + 10」で自動採番し、編集時のみ数値入力で変更する。既存データは現行の表示順のまま 10 刻み (10, 20, 30, ...) で order_no をバックフィルし、適用直後の見た目を変えず、間への挿入余地を残す

## Sort

- viewer 一覧: `order_no ASC, first_released_on DESC, release_group_id ASC` (キーセットカーソルも同じ 3 キー)
- admin 検索: `order_no ASC, first_released_on DESC, title ASC` (リリース未登録グループは末尾)

## Scope

- `src/contracts/src/admin/releases/` (ReleaseGroup / ReleaseGroupSummary / Create・Update リクエスト)
- `src/server/database/atlas/schemas/release-groups.my.hcl` (order_no int unsigned default 1)
- `src/server/packages/Release/` (Domain モデル / IntegrityService / Repository / Admin・Viewer QueryService / カーソル)
- `src/server/app/Http/Presenters/` の ReleaseGroup 変換
- `src/admin/` の release-group フォーム・一覧・BFF ルート
- 既存データのバックフィル SQL (migration.sql)

## Non-Scope

- viewer API 契約への orderNo 追加 (viewer は並び順をサーバーに委ねており値そのものは使わない)
- 並べ替え UI (ドラッグ & ドロップ等)。当面は編集フォームでの数値入力
- 採番の振り直し (隙間が枯渇した場合は編集フォームで手動調整する)

## Steps

- [x] 1. atlas schema に order_no 追加、migration.sql (現行ソート順で 10 刻みのバックフィル) を用意
- [x] 2. contracts に orderNo を追加し `contract:compile:admin` / `api:generate:admin` / `admin:generate` で再生成
- [x] 3. サーバー実装 (Domain / UseCase / Repository / QueryService / Presenter / viewer カーソル)
- [x] 4. admin UI (DetailView の表示順入力 / SearchList の表示順列 / BFF ルート)。CreateForm は入力なしでサーバー側自動採番 (最大値 + 10)
- [x] 5. テスト更新と検証 (api:test 596 件 / phpstan / arkitect / ecs / contract / admin lint・build)
