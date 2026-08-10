# Issue — Release の MusicBrainz 方式リモデリング

## Title

Release を ReleaseGroup → Release → Medium → Track の階層へ再モデリングする

## Background

現行の Release は単層で、`distribution_type`(Digital/Physical/Other の単一 enum)しか持たない。このため

- 同一作品の「配信版 / CD 通常盤 / 限定盤」が互いに無関係な別レコードになり、束ねられない
- 仕様(`docs/product-specs/20260503-song-media-admin-phase1/models.md`)にあった `limited` が enum に無く、複数流通形態も表現できない
- CD+DVD のような複合媒体、盤ごとの収録差(ボーナストラック)を表現できない

`docs/exec-plans/completed/20260508-release-foundation.md` の Decision Log では「版違いは別 Release として登録」と割り切っていたが、これを MusicBrainz 方式の ReleaseGroup 導入で正式に解消する

## Goal

- ReleaseGroup(作品)/ Release(版・盤)/ Medium(盤内媒体) / Track(収録曲)の 4 階層モデルへ移行する
- format(媒体種別) は Medium の属性とし、`ReleaseDistributionType` を廃止する
- ReleaseGroup の一覧は傘下 Release の最古 released_on(first release date 方式)でソートできる

## Scope

- `src/contracts/src/admin/releases/`(release-groups + releases へ再設計)と生成物の更新
- `src/server/packages/Release`(ReleaseGroup 集約新設、Release 集約再構成)
- `src/server/database/atlas/schemas/`(release_groups / releases / release_media / release_tracks)
- `src/server/app`(Controller / Presenter / routes / DI / AuditTargetType)
- `src/admin`(BFF ルートと画面の 2 階層化)
- サーバーのフィーチャー・インテグレーションテスト

## Non-Scope

- viewer の Release 対応(契約未接続・モックのまま。今回のモデルで将来設計する)
- 品番(catalog number)・レーベル(label)・ジャケット画像
- データ移行(本番ユーザー不在のため再投入で対応)

## Acceptance Criteria

- ReleaseGroup CRUD + 検索、Release CRUD が admin API・管理画面から行える
- 限定盤ボーナストラックや CD+DVD 複合盤をモデルとして表現できる
- `mise run ecs / phpstan / arkitect / test` と contracts・admin の各チェックが通る
