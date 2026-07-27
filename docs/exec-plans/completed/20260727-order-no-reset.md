# Plan — order_no 採番リセット

## Title

表示順 (order_no) を現行順のまま 10 刻みで振り直す admin 操作を追加する

## Status

completed

## Background

Song / Person / SongTag は作成時に `max(order_no) + 10` で採番し、編集時に隙間へ挿入できる。ReleaseGroup も 10 刻みでバックフィル済みで、手動編集で隙間を使う想定だった。隙間が枯渇したり値が散らかったりしたとき、現行の相対順を保ったまま 10, 20, 30, ... へ振り直す手段がなかった（ReleaseGroup 追加時は Non-Scope）。

## Goal

admin の一覧画面から、対象リソースの `order_no` を現行表示順のまま 10 刻みで振り直せるようにする。

## Scope

- 対象リソース: Person / SongTag / Song / ReleaseGroup
- `src/contracts` (admin API: `POST .../reset-order-numbers`)
- `src/server` (UseCase / Repository / Controller / Presenter / Route / テスト)
- `src/admin` (BFF + 一覧の操作ボタン)

## Non-Scope

- Release（クライアント指定の表示順で、マスタの +10 採番ではない）
- 関連テーブル側の order_no（song_persons / song_media_links / song_taggings / release tracks）
- ドラッグ&ドロップ並べ替え UI
- 新規の AuditAction 追加（振り直しで値が変わった行は既存の `update` として記録）

## Acceptance Criteria

- 現行 `order_no` 昇順（同値は主キー昇順）の相対順を保ったまま 10, 20, 30, ... に更新される
- すでに 10 刻みで歯抜けのない状態なら `updatedCount` は 0（または変更行のみカウント）
- Write 権限が必要
- admin 一覧のアクション行から確認ダイアログ付きで実行でき、完了後に一覧が再取得される

## Steps

- [x] 1. TypeSpec に reset-order-numbers を追加し OAS / クライアントを再生成
- [x] 2. Person / SongTag / Song / ReleaseGroup に Repository・UseCase・Controller・Presenter・Route を実装
- [x] 3. Feature / Integration テストを追加
- [x] 4. admin BFF と SearchList に採番リセットボタンを追加
- [x] 5. lint / test / build を通し、PR を出す

## Decision Log

- 2026-07-27: 対象は Person / SongTag / Song / ReleaseGroup。Release と関連テーブルの order_no は除外
- 2026-07-27: 振り直し順は現行 `order_no ASC, <pk> ASC`。相対順を変えず隙間だけ戻す
- 2026-07-27: エンドポイントはリソースごとの `POST /{resource}/reset-order-numbers`。レスポンスは `{ updatedCount }`
- 2026-07-27: Song は集約の full save を避け、`order_no` 列だけ更新する Repository メソッドを用意する
- 2026-07-27: 共通実装は `Support\Infrastructures\Database\OrderNoResetter` に集約

## Validation

- Feature: Person / SongTag / Song / ReleaseGroup の reset テスト OK
- Integration: OrderNoResetterTest OK
- `composer phpstan` / `arkitect` / `ecs` OK
- admin `lint:check` / `style:check` / `build` OK
- contracts `format:check` / `test` OK
