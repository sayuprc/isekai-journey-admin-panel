# Plan — Release format の階層引き上げ

## Title

format を Medium から Release へ引き上げ、媒体違い同内容リリースの二重登録を解消する

## Status

completed

## Background

現行モデル (20260704-release-remodeling) は format を Medium の属性としているため、「CD と配信で同日・同内容」のリリースは 1 ReleaseGroup に 2 Release を登録する必要がある。トラックリストの二重入力が面倒で、viewer でも同内容のアコーディオンが 2 つ並び冗長。実データでは CD と配信は同日・同内容・同ジャケットが大多数のため、提供形態の複数性を Release 自身に持たせる

## Goal

「CD と配信で同時発売・同内容」を 1 Release (formats=[Cd, Digital]) で表現できるようにし、admin の入力と viewer の表示を簡素化する。内容・日付が食い違うケースは従来通り別 Release で表現する

## Model

```
Release   name / releasedOn / formats: ReleaseFormat[] (1つ以上・重複禁止) / ...
└─ Medium  position / name (自由テキスト・nullable) / tracks   ※format 廃止
   └─ Track  trackNo / songId / title
```

- `ReleaseFormat` は旧 `MediumFormat` の改名 (値は Digital=1, Cd=2, Dvd=3, BluRay=4, Other=99 のまま)
- Medium.name はライブ盤の「CD1」「Blu-ray」等の Disc 表示ラベル
- formats と media 枚数の整合検証はしない (盤の切り方と提供形態は独立した事実)

## Scope

- `src/contracts/src/admin/releases/` と `src/contracts/src/viewer/releases/`
- `src/server/database/atlas/schemas/` (release_formats 新設 / release-media 変更)
- `src/server/packages/Release/` (Domain / Application / Infrastructures)
- `src/server/app/Http/Presenters/` の Release / ReleaseGroup 変換
- `src/admin/` の Release フォームと ReleaseGroup 詳細
- `src/viewer/` のリリース一覧・詳細表示
- 既存データのバックフィル SQL

## Non-Scope

- Release 複製 API (既存の `sourceReleaseId` プリフィルで足りる)
- 既存の「CD 盤 / 配信盤」2-Release ペアの自動統合 (候補列挙 SQL のみ用意し、統合は admin で手動)
- Medium.name のマスタ化 (将来検討)
- viewer の表示構造変更 (single フラット / 複数版アコーディオンは現状維持)

## Acceptance Criteria

- admin で formats に CD と配信をチェックした Release を 1 つ作成でき、get/update でも formats が往復する
- formats 未選択 (空) の create/update は 422 (バリデーションエラー) になる
- viewer 詳細で Release の format バッジが `CD・配信` のように表示される (Digital の表示名は「配信」)
- viewer 一覧カードのメタ行に formats が併記される
- 複数 Disc の Release で Medium.name があれば `DISC 2 · Blu-ray` のように表示され、無ければ `DISC 2` のみ
- バックフィル SQL 適用後、既存 Release の formats が旧 media format の和集合と一致する
- `mise run contract:*` / `api:ecs` / `api:phpstan` / `api:arkitect` / `api:test` / admin・viewer の lint / build が全て通る

## Steps

- [x] 1. contracts: admin/viewer の `MediumFormat` → `ReleaseFormat` 改名、Release モデルに `formats` 追加、Medium から `format` 削除・`name: string | null` 追加、`mise run generate` で再生成
- [x] 2. atlas: `release-formats.my.hcl` 新設 (PK(release_id, format)・FK CASCADE)、`release-media.my.hcl` から format 削除・name varchar(255) NULL 追加
- [x] 3. migration: バックフィル SQL (`INSERT INTO release_formats SELECT DISTINCT release_id, format FROM release_media` を DDL 適用前に実行する手順) と、同一 group・同一 released_on の統合候補列挙 SQL を `docs/exec-plans/active/20260706-release-formats/migration.sql` に用意
- [x] 4. server domain: `ReleaseFormat` VO 改名 + `ReleaseFormats` コレクション VO (空・重複禁止)、`Medium` を position+name+tracks に再定義、`Release` に formats 追加、`MediumName` VO 追加
- [x] 5. server application/infra: Release 4 UseCase の入出力に formats / medium name を反映、`ReleaseRepository` の保存・復元、admin ReleaseGroup 詳細サマリの formats を media 導出から release 直参照へ変更、viewer `ReleaseGroupQueryService` の read model 更新
- [x] 6. server http: Release / ReleaseGroup の Presenter・Converter を新契約に合わせる
- [x] 7. server tests: EntityFactory / Feature / Integration / Unit を新モデルに追随 (formats 空 400・name null 往復を含む)
- [x] 8. admin: Release Create/Update フォームに formats チェックボックス追加、`MediaEditor` から format セレクト削除・name 入力追加、ReleaseGroup `DetailView` のサマリ表示追随
- [x] 9. viewer: 詳細の format 表示を `release.formats` に差し替え (Digital→「配信」)、Disc 見出しを `DISC {position} · {name}` に変更、一覧カードのメタ行に formats 併記
- [x] 10. 全検証実行、既存データへバックフィル適用、plan を completed へ移動

## Decision Log

- 2026-07-06: 表示/UX のみの解決 (viewer 自動グルーピング + 複製ボタン) ではなくモデル変更を選択。二重入力の根本原因を除去するため
- 2026-07-06: 複数性の置き場所は Medium.formats (複数化) ではなく Release.formats。表示・入力の単位が Release であり、Medium は「トラックリストの区切り」という単一責務に戻すため
- 2026-07-06: Medium に自由テキスト name (nullable) を追加。旧 `DISC 2 · DVD` 見出しは format の流用だったが、「CD1」「特典 Blu-ray」等 enum で表現できない実態があるため。将来のマスタ化余地は残す
- 2026-07-06: enum はデータ側で細粒度を維持 (物理/配信の 2 値に丸めない)。丸めは表示側でいつでもできるが逆は不可逆のため。viewer バッジは format 名そのまま (Digital のみ「配信」表記)
- 2026-07-06: formats は子テーブル `release_formats` (song_taggings と同じ多値表現の前例)。順序なし集合、表示順は viewer が enum 値で決定
- 2026-07-06: 既存 CD/配信ペアの統合は手動。自動統合はトラックリスト一致判定と残すフィールドの選択が必要で、件数に対してリスクが釣り合わないため
- 2026-07-06: 今回から既存データ互換を考慮する方針に転換 (ユーザー表明)。破壊的 DDL にはバックフィル SQL を添える
- 2026-07-06: enum 名は `ReleaseFormat`。旧 `ReleaseDistributionType` の復活は紛らわしいため避けた
- 2026-07-06: formats 空のエラーは既存の入力検証と同じ 422 (InvalidInputError)。AC の 400 は実態に合わせ修正
- 2026-07-06: formats の応答順は値順 (release_formats を format 昇順で読む)。入力順は保存しない
- 2026-07-06: 既存データの統合候補 3 ペアはすべて正当な別版 (α/β 盤等) で、手動統合の作業は発生しなかった

## Validation

- `mise run contract:format:check` / `contract:test` / `contract:compile:admin` / `contract:compile:viewer`: OK
- `mise run api:ecs` / `api:phpstan` / `api:arkitect`: OK
- `mise run api:test`: 593 tests / 2449 assertions OK (skip 3 は既存)
- `bun --filter admin lint:check` / `style:check` / `build`: OK
- `mise run viewer:check` / `bun --filter viewer build`: OK (2883 ページ)
- local DB: release_formats バックフィル 59 行 (57 リリース・media 和集合と一致)、atlas 適用済み
- code-reviewer レビュー実施: 警告 1 件 (update 側 formats 空テスト欠如) を修正済み、ほか軽微な提案のみ
- testing DB: atlas 適用済み
- 統合候補列挙 SQL: 3 ペア検出、すべて正当な別版のため統合作業なし
