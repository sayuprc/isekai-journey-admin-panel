# Title

Media パッケージ境界の整理

## Status

completed

## Background

`docs/product-specs/20260503-song-media-admin-phase1/README.md` と `models.md` では、`Media` は将来 `Song` と `Event` の両方から参照される独立概念、`SongMediaLink` は楽曲文脈を表す関係エンティティとして定義されている。これに対して現状の基礎実装では、`src/server/packages/Media/Domain/Models/SongMediaType.php` と `src/server/packages/Media/Domain/Models/SongMediaLink.php` が `Media` パッケージに置かれ、`SongMediaLink` は `Song\Domain\Models\SongId` に依存している。

この配置は基礎実装を先に成立させるには十分だったが、今後 `Media` の repository / use case / admin 連携を進めると、どのパッケージが `Song` 文脈の関係を所有するのかが曖昧なまま広がる。`MediaType` と `SongMediaType` の責務分離自体は仕様で固まっているため、次に整理すべき論点は「型の意味」と「パッケージの所有境界」を一致させることにある。

## Goal

`SongMediaType` と `SongMediaLink` をどのモジュールが所有するべきかを明確にし、その判断に合わせて server 側のドメインモデル配置を一貫させる。`Media` の基礎スキーマや enum 値定義は維持しつつ、後続の API / admin 実装で境界がぶれない状態を作る。

## Scope

- `src/server/packages/Media/Domain/Models`
- `src/server/packages/Song/Domain/Models`
- `src/server/app/Models/Media/Media.php`
- `src/server/app/Models/Song/SongMediaLink.php`
- `src/server/composer.json`
- `docs/product-specs/20260503-song-media-admin-phase1/README.md` と `models.md` の解釈に基づく `Media` / `SongMediaLink` の責務整理

## Non-Scope

- `media` / `song_media_links` テーブル定義や enum 値の追加・変更
- `Media` CRUD、検索 API、admin UI の実装
- `Release`、`Event`、`EventMediaLink` など Phase 2 以降の具体実装
- `Song` 本体の属性設計や既存 `SongType` / `SongPersonRole` の見直し
- TypeSpec、OpenAPI、generated code の更新

## Acceptance Criteria

- `SongMediaType` と `SongMediaLink` の所有先が 1 つの方針で説明でき、その方針に沿って `src/server/packages/Media` と `src/server/packages/Song` の配置が一貫している
- `Media` 固有の概念と楽曲文脈の概念が server 側コード上で分離され、`MediaType` と `SongMediaType` の責務が混同されていない
- パッケージ境界の変更が必要な場合、`src/server/app/Models/Media/Media.php`、`src/server/app/Models/Song/SongMediaLink.php`、関連 namespace / import、`src/server/composer.json` が整合した状態に更新され、重複定義が残らない
- `docs/product-specs/20260503-song-media-admin-phase1/README.md` と `models.md` が定義する `Media` 独立性と `SongMediaLink` の楽曲文脈責務を壊さない

## Steps

1. ✅ `docs/product-specs/20260503-song-media-admin-phase1/README.md` と `models.md`、および現状の `src/server/packages/Media/Domain/Models/{Media,MediaType,SongMediaType,SongMediaLink}.php` を照合し、`Media` package には `Media` 単体概念だけを残し、`SongMediaType` / `SongMediaLink` は `Song` package が所有する方針を固定する。
2. ✅ `src/server/packages/Song/Domain/Models` 配下に、楽曲文脈のメディア関係を置く新しい配置を追加する。少なくとも `SongMediaType.php` と `SongMediaLink.php` を `Song` namespace へ移し、`SongId`・`MediaId`・`OrderNo` への依存が `Song` 側から読める形へ整理する。
3. ✅ `src/server/packages/Media/Domain/Models` から `SongMediaType.php` と `SongMediaLink.php` を取り除き、`Media.php`、`MediaId.php`、`MediaTitle.php`、`MediaUrl.php`、`MediaType.php` だけが残る状態にする。`Media` package から `Song` 文脈への直接依存をなくす。
4. ✅ `src/server/app/Models/Media/Media.php` と `src/server/app/Models/Song/SongMediaLink.php` を見直し、Eloquent 側の relation 所有境界が新方針と矛盾しないことを確認する。既存の `Song` 側 Eloquent 配置で整合していたため、コード変更は行わない。
5. ✅ `rg -n "SongMediaType|SongMediaLink" src/server/packages src/server/app` で参照箇所を洗い、namespace / import を新配置へ張り替える。`src/server/composer.json` は既存の `Song\\` と `Media\\` の PSR-4 で解決できるため、namespace ルート変更がない限り編集しない。
6. ✅ `src/server/packages/Song/Application/Assemble/*` は、このタスクでは新しいドメイン本体を増やさず据え置く。`メディア付き楽曲` のような複合概念が必要になった場合は、後続で Application / Assemble で `Song`・`SongMediaLink`・`Media` を束ねる前提に留める。
7. ✅ 実装後は、`Media` package に `SongId` 依存や `SongMedia*` 定義が残っていないこと、`Song` package 側に重複や循環参照がないこと、変更範囲が対象ファイル群に閉じていることを確認する。

## Decision Log

- 2026-05-05: `Media` package は横断参照の主役として `Media` 単体概念だけを持ち、楽曲文脈でしか意味を持たない `SongMediaType` / `SongMediaLink` は `Song` package が所有する。仕様上の責務と code 上の依存方向を一致させるため。
- 2026-05-05: `SongMediaLink` は `Song` と `Media` を結ぶ関係エンティティとして残し、`メディア付き楽曲` のような新しい domain 本体は作らない。複合表現が必要な場合は後続の Application / Assemble 層で扱う。
- 2026-05-05: `SongMediaType` は `MediaType` に統合せず、楽曲文脈専用 enum として `Song` 側に移す。`MediaType` がメディア形式、`SongMediaType` が楽曲との関係分類という責務分離を保つため。
- 2026-05-05: `src/server/app/Models/Song/SongMediaLink.php` は既に `Song` 側にあり、Eloquent の所有境界は大筋で正しいため、変更は必要最小限の import / docblock 調整に留める。
- 2026-05-05: `src/server/composer.json` は `Song\\` と `Media\\` の PSR-4 が既に定義されているため、今回の境界整理だけでは変更不要とみなす。不要な差分を避けるため。
- 2026-05-05: `SongMediaType` / `SongMediaLink` の配置先は `src/server/packages/Song/Domain/Models/Media` とし、既存の `Song` package にある `Persons` / `Tags` と同様に楽曲文脈の下位概念をサブディレクトリで分ける。
- 2026-05-05: `Song\Domain` から `Media\Domain\Models\MediaId` へ直接依存すると Arkitect の境界ルールに違反するため、`SongMediaLink` の `media_id` は `string` 参照として保持する。`Media` 集約の所有は維持しつつ、`Song` 側は外部集約 ID を値として扱う。

## Validation

- `rg -n "SongMediaType|SongMediaLink" src/server/packages/Media src/server/packages/Song src/server/app` を実行し、`SongMedia*` 定義と参照が `Song` 側に集約され、`Media` 側に残骸がないことを確認する。
- `rg -n "SongId" src/server/packages/Media` を実行し、`Media` package から `Song` 文脈への直接依存が消えていることを確認する。
- `mise run api:phpstan -- packages/Media packages/Song app/Models/Media app/Models/Song` を実行し、namespace 変更に伴う静的解析エラーがないことを確認する。
- 必要に応じて `mise run api:arkitect` を実行し、パッケージ境界の制約に反する依存が増えていないことを確認する。
- `git diff --name-only` で、変更が `docs/exec-plans/active/20260505-media-package-boundary.md` で定めた対象に概ね閉じていることを確認する。
