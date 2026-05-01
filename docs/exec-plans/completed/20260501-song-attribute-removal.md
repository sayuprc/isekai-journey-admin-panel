# Title

楽曲属性削除

## Status

completed

## Background

楽曲属性は楽曲タグへ置き換える前提になっているが、現状は API 契約、サーバー実装、管理画面、DB スキーマに `song-attributes` と `attribute` フィールドが残っている。新旧表現が併存すると、楽曲管理の入力・検索・表示条件が二重化し、今後の実装と運用判断を複雑にする。

## Goal

コード上から楽曲属性の概念と参照経路を削除し、楽曲は種別と楽曲タグのみを扱う状態に整理する。

## Scope

- `src/contracts/src/admin/songs/*.tsp`, `src/contracts/src/admin/song-attributes/*`, `src/contracts/src/admin/main.tsp` から楽曲属性の契約と `song-attributes` API を外す
- `src/server/packages/Song/**`, `src/server/routes/admin.php`, `src/server/database/atlas/schemas/songs.my.hcl` から楽曲属性のドメインモデル、検索条件、API ルート、永続化項目を外す
- `src/admin/src/components/song/*`, `src/admin/src/server/routes/songs.ts`, `src/admin/src/server/routes/song-attributes.ts`, `src/admin/src/pages/song-attributes/index.astro`, `src/admin/src/components/Sidebar.tsx` から楽曲属性 UI/BFF を外す
- 上記 SoT 変更に追随する生成物・テストの更新対象として `src/contracts/generated/oas/`, `src/server/Generated/`, `src/admin/src/generated/`, `src/server/tests/**` を含む

## Non-Scope

- 楽曲タグ自体の仕様追加や名称変更
- 既存データ移行の運用手順策定
- 閲覧サイト (`src/viewer`) の新機能追加

## Acceptance Criteria

- 楽曲 API 契約から `SongAttribute`, `SongAttributeValue`, `attribute`, `attributeValue`, `/song-attributes` が除去されている
- 管理画面で楽曲の作成・編集・検索・サイドバーに楽曲属性の入力欄、検索条件、一覧導線が存在しない
- サーバー実装とテストコードに楽曲属性のドメイン型、検索条件、ルート定義への依存が残っていない
- 楽曲永続化定義から属性カラムが削除対象として整理され、少なくともコードベース上で属性カラム前提の参照がなくなっている

## Steps

1. ✅ `src/contracts/src/admin/main.tsp`, `src/contracts/src/admin/song-attributes/service.tsp`, `src/contracts/src/admin/song-attributes/transport.tsp`, `src/contracts/src/admin/songs/domain.tsp`, `src/contracts/src/admin/songs/service.tsp`, `src/contracts/src/admin/songs/transport.tsp` を更新し、`song-attributes` 名前空間・`SongAttribute*` 型・楽曲の `attribute` / `attributeValue`・検索クエリ `attribute` を削除する。以降の実装はこの SoT 変更を前提に進める。
2. ✅ `src/server/packages/Song/Domain/**`, `src/server/packages/Song/Application/**`, `src/server/packages/Song/Infrastructures/**`, `src/server/app/Models/Song/Song.php`, `src/server/database/atlas/schemas/songs.my.hcl` を更新し、楽曲属性 enum・入力 DTO・検索条件・永続化マッピング・Assembler の属性処理を削除する。`Song` 集約と検索結果は種別・タグのみを持つ形に揃える。
3. ✅ `src/server/app/Providers/Domain/SongServiceProvider.php`, `src/server/routes/admin.php`, `src/server/packages/Song/Route/SongAttributeRouteMap.php`, `src/server/app/Http/Controllers/Api/SongAttribute/*`, `src/server/app/Http/Presenters/Api/SongAttribute/*` を削除または整理し、楽曲属性一覧 API の DI・ルート・Controller・Presenter 配線を除去する。
4. ✅ `src/server/app/Http/Presenters/Api/Song/Converter.php`, `src/server/app/Http/Presenters/Api/Song/SearchPresenter.php`, `src/server/packages/Song/Application/Query/SongSummary.php` を更新し、レスポンス変換から楽曲属性の組み立てを削除する。契約変更後の `Song` / `SongSummary` 形状へ整合させる。
5. ✅ `src/admin/src/server/index.ts`, `src/admin/src/server/routes/song-attributes.ts`, `src/admin/src/server/routes/songs.ts` を更新し、BFF から楽曲属性 API 呼び出し・スキーマ・レスポンス整形・ルート登録を削除する。作成フォーム、編集フォーム、検索 API は種別とタグだけを返す形にする。
6. ✅ `src/admin/src/components/song/CreateForm.tsx`, `src/admin/src/components/song/EditableForm.tsx`, `src/admin/src/components/song/SearchList.tsx`, `src/admin/src/pages/song-attributes/index.astro`, `src/admin/src/components/Sidebar.tsx` を更新し、楽曲属性の入力欄・検索条件・一覧表示列・専用ページ導線を削除する。楽曲管理 UI は種別・表示設定・タグ操作のみを残す。
7. ✅ `src/contracts/generated/oas/**`, `src/server/Generated/**`, `src/admin/src/generated/**` を再生成し、OpenAPI/クライアント/サーバー生成物から `SongAttribute` と `/song-attributes` を除去する。生成後に手書きコードとの型不整合を解消する。
8. ✅ `src/server/tests/Feature/Api/SongAttribute/*`, `src/server/tests/Feature/Api/Song/SearchSongTest.php`, `src/server/tests/Unit/Song/**`, `src/server/tests/Support/Domain/EntityFactory.php` を更新し、楽曲属性 API テストを削除しつつ、楽曲作成・検索・整合性テストを新しい契約に合わせる。必要なら管理画面側の型検査も通して削除漏れを確認する。

## Decision Log

- 2026-05-01: 削除の起点は `src/contracts` に置く。API 契約が SoT であり、ここを先に消すことで server/admin/generated の追随箇所を明確にできるため。
- 2026-05-01: 楽曲属性一覧 API は互換維持せずに配線ごと削除する。要件が「楽曲タグへ置き換わるためコード上から削除」であり、残置すると UI と依存コードの温床になるため。
- 2026-05-01: DB スキーマは「削除対象として整理」までを本計画に含め、少なくとも Atlas 定義とリポジトリ参照を合わせて消す。これによりコード上で属性カラム依存が再流入しない状態を先に作れるため。
- 2026-05-01: 管理画面は専用の `song-attributes` ページ削除だけでなく、楽曲一覧・作成・編集の BFF レスポンス形状も同時に縮小する。UI だけ消すと unused field や generated 型の不整合が残るため。
- 2026-05-01: `bunx tsc --noEmit` は `src/admin/src/components/creator/SearchList.tsx`, `src/admin/src/components/performer/SearchList.tsx`, `src/admin/src/server/routes/creators.ts`, `src/admin/src/server/routes/performers.ts` の既存型エラーで失敗した。今回の楽曲属性削除差分とは独立しているため、実装完了判断は Song 周辺の grep・生成・server テスト通過を優先した。

## Validation

- `rg -n "song-attributes|SongAttribute|attributeValue|attribute" src/contracts/src/admin src/server src/admin -g '!**/Generated/**' -g '!**/generated/**'` を実行し、今回の対象に関する参照が削除済みであることを確認する。
- `mise run generate:server` と `mise run generate:client:admin` を実行し、生成物が最新化され、`src/server/Generated` と `src/admin/src/generated` に楽曲属性 API/型が残らないことを確認する。
- `mise run test src/server/tests/Unit/Song` と `mise run test src/server/tests/Feature/Api/Song` を実行し、属性削除後も楽曲ユースケースと API 振る舞いが通ることを確認する。
- `bunx tsc --noEmit` を `src/admin` 相当の型検査として実行し、管理画面のフォーム・検索画面・BFF に属性削除起因の型エラーがないことを確認する。
- 管理画面で `/songs`, `/songs/new`, `/songs/:songId/edit` とサイドバーを確認し、楽曲属性の入力欄、検索条件、一覧列、`/song-attributes` 導線が消えていることを目視確認する。
