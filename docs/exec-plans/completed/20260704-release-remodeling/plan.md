# Plan — Release の MusicBrainz 方式リモデリング

## Title

Release を ReleaseGroup → Release → Medium → Track の階層へ再モデリングする

## Status

completed

## Model

```
ReleaseGroup（作品）  title / type(Single|Album|Ep|Other) / description / isDisplay
└─ Release（版・盤）  name(版名) / releasedOn / description / isDisplay
   └─ Medium          position / format(Digital|Cd|Dvd|BluRay|Other)
      └─ Track        trackNo / songId（Song 集約を ID 参照）
```

- ReleaseGroup と Release は別集約（Release が Medium・Track を内包）。両方とも `packages/Release` に置く
- ReleaseGroup は released_on を持たず、一覧ソートは傘下 Release の最古 released_on を導出（first release date 方式）
- `ReleaseDistributionType` は廃止。format は Medium の属性
- ドメイン検証: Medium の position 重複禁止 / Medium 内の trackNo 重複禁止 / Release 全体で songId 重複禁止

## DB（Atlas）

- `release_groups`: release_group_id PK / title / type / description / is_display / timestamps
- `releases`: release_id PK / release_group_id FK(RESTRICT) / name / released_on / description / is_display / timestamps
  - `type`・`distribution_type`・`title` は廃止
- `release_media`: PK(release_id, position) / format / FK release_id → releases CASCADE
- `release_tracks`: PK(release_id, position, track_no) / song_id / UNIQUE(release_id, song_id) / 複合 FK(release_id, position) → release_media CASCADE / song_id → songs RESTRICT
- `release-track-entries.my.hcl` は削除

## API（admin 契約）

- `/admin/v1/release-groups`: create / get / update / delete / search
  - search: title / type / isDisplay / page / perPage。応答に firstReleasedOn（導出・nullable）を含む
  - get: 傘下 Release のサマリ read model（releaseId / name / releasedOn / isDisplay / formats）を同梱
- `/admin/v1/releases`: create（releaseGroupId 指定）/ get / update / delete。search は廃止（発見は group 経由）
  - get: 収録曲 read model（mediumPosition / trackNo / songId / title）を同梱
- `AuditTargetType` に `ReleaseGroup` を追加（contracts と server 両方）
- Permission は既存の `ReadRelease` / `WriteRelease` を ReleaseGroup にも使う

## Steps

1. contracts: `src/contracts/src/admin/releases/` を release-groups + releases に再設計し `mise run generate`
2. atlas: スキーマ 4 テーブルを再定義
3. server domain: ReleaseGroup 集約新設・Release 集約再構成（Medium / Track / MediumFormat / 検証）
4. server application: ReleaseGroup 5 UseCase + Release 4 UseCase、read model クエリサービス
5. server infra: ReleaseGroupRepository / ReleaseRepository / QueryService
6. server http: Controller / Presenter / routes / DI / AuditTargetType
7. server tests: EntityFactory・Feature・Integration の再構成
8. admin: BFF ルート（release-groups.ts / releases.ts）と画面の 2 階層化
9. 全チェック実行・exec-plan を completed へ移動

## Decision Log

- 2026-07-04: トラックリストは Release ごと（版ごとの収録差を表現）。format は Medium 属性（CD+DVD 複合盤対応）
- 2026-07-04: UseCase の名前空間は Song/Tag の前例に合わせ `UseCase/Group/*`（ReleaseGroup 用）とした
- 2026-07-04: ReleaseGroup の検索は CQRS の read model（ReleaseGroupSearchQueryService、firstReleasedOn 導出込み）に一本化し、Repository は find/save/delete のみ
- 2026-07-04: BusinessLogicError(400) を返しうる updateRelease / deleteReleaseGroup の契約に BadRequest を追加（レスポンスバリデータ対策）
- 2026-07-04: admin の楽曲追加 UI は「追加先媒体」セレクト＋単一検索フォーム（媒体ごとの検索フォームは持たない）
- 2026-07-04: 盤層の命名は Medium（複数形 media）。テーブルは既存 `media` と衝突するため `release_media`
- 2026-07-04: 品番・レーベルは見送り。viewer は非対象（契約未接続のまま）
- 2026-07-04: /releases/search は廃止し、一覧・検索は release-groups に一本化
- 2026-07-04: ReleaseGroup 削除は傘下 Release が存在する場合 UseCase で BusinessLogicError（DB は RESTRICT）

## Validation

- `mise run contract:format:check` / `contract:test` / `contract:compile:*`
- `mise run ecs` / `phpstan` / `arkitect` / `test`
- `cd src && bun --filter admin lint:check && bun --filter admin style:check && bun --filter admin build`
