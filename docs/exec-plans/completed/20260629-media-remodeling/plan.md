# Execution Plan — Plan

問題定義は同ディレクトリの `issue.md` を参照する

## Title

Media モデルを「素の状態＋分類1軸」へ作り直す(#835 platform/thumbnail 巻き戻し)

## Status

completed

## 現状メモ(着手前の前提)

`refactor/media` ブランチの作業ツリーには本リファクタの**部分的な下書き**が未コミットで存在する。完了済みと未完了が混在しているため、本計画は「あるべき完成状態」を起点に全工程を記述する。実装時は下書きの差分を取り込みつつ、以下の未完／不整合を必ず是正すること

- 完了済み(下書きに反映済み): サーバー PHP 側の platform/format/thumbnail 撤去(`Media.php`・`MediaRepository.php`・`MediaSearchCriteria.php`・各 UseCase・QueryService・Presenter・`Generated/*`)、`MediaType.php` の新6値化、契約からの `MediaFormat`/`MediaPlatform`/`mediaThumbnailUrl` 型・フィールド削除、DB `media.my.hcl` の列・CHECK 削除
- **未完／不整合(要対応)**:
  - 契約 `packages/media/domain.tsp` の `MediaTypeValue` が旧値(`Video`/`Article`/`SocialPost`/`OfficialPage`/`Other`)のまま。新6値へ未更新(Step 1)
  - viewer `features/media/labels.ts` が url 導出ヘルパー未拡張。`PostCard.astro`・`MediaDetailContent.astro` が削除済み契約の `platform`/`thumbnailUrl`/`format` を参照したまま(Step 5)
  - admin `CreateForm.tsx`・`EditableForm.tsx`・`SearchList.tsx` が `MediaFormatValue`/`MediaPlatformValue`/`thumbnailUrl` を参照したまま(Step 6)

## Steps

実装順序の依存関係: **契約(Step 1)→ generate(Step 2)→ サーバー/DB(Step 3-4)・viewer(Step 5)・admin(Step 6)→ テスト(Step 7)**。Step 1 を起点に生成物が更新されるため、Step 2 完了前にフロント/サーバーの型参照を直しても噛み合わない

### 1. 契約(TypeSpec)を確定する — 起点 ✅

`src/contracts/src/` を編集する

- `packages/media/domain.tsp`:
  - `MediaTypeValue` を **`Mv: 1` / `AudioVideo: 2` / `LiveStream: 3` / `Short: 4` / `Post: 5` / `Other: 99`** へ再定義する。`@example` の値も `MediaTypeValue.Mv`(name: "MV")等へ更新
  - `MediaFormat(Value)` / `mediaFormatName` / `MediaPlatform(Value)` / `mediaPlatformName` / `mediaThumbnailUrl` スカラ・enum・model を削除(下書きで削除済みなら確認のみ)
- `viewer/media/domain.tsp`: `Media` から `format` / `platform` / `thumbnailUrl` フィールドと `@example` の該当行を削除。`type` の example 値を新値(例: `#{ name: "MV", value: MediaTypeValue.Mv }`)へ更新
- `viewer/media/transport.tsp`: example 中の `format` / `platform` / `thumbnailUrl` を削除
- `admin/media/domain.tsp`: `Media` から `format` / `platform` / `thumbnailUrl` を削除
- `admin/media/transport.tsp`: `MediaCreateRequest` / `MediaUpdateRequest` から `formatValue` / `platformValue` を削除
- `admin/media/service.tsp`: `searchMedia` の `@query format?: MediaFormatValue` を削除
- 併せて `viewer/songs/*` `admin/songs/domain.tsp` の Media 参照 example に旧フィールドが残っていれば除去(下書きの差分に含まれるため整合させる)

検証: `mise run contract:format` → `mise run contract:format:check` → `mise run contract:test` → `mise run contract:compile:all`(OAS 再生成)

### 2. 生成物を更新する(generate) ✅

`mise run generate`(`api:generate` + `admin:generate` + `viewer:generate` を実行)

- サーバー: `src/server/Generated/{Admin,Viewer}/lib/Model/MediaFormat*.php` `MediaPlatform*.php` が削除され、`Media*Request.php` 等から `formatValue`/`platformValue`/`thumbnailUrl` が消えることを確認
- フロント: `src/admin/src/generated/`・`src/viewer/src/generated/`(`types.gen.ts` / `index.ts`)から `MediaFormatValue`/`MediaPlatformValue`/`thumbnailUrl` が消え、`MediaTypeValue` が新6値になることを確認

依存: Step 1 完了必須

### 3. サーバードメイン／インフラを 6 フィールドへ縮約する ✅

`src/server/packages/Media/`(下書き反映済みなら確認・補完)

- 削除: `Domain/Models/MediaPlatform.php`・`MediaFormat.php`・`MediaThumbnail.php`
- `Domain/Models/MediaType.php`: enum を新6値(`Mv`/`AudioVideo`/`LiveStream`/`Short`/`Post`/`Other`)+ `getName()`(MV/音源動画/ライブ・配信/ショート/投稿/その他)へ
- `Domain/Models/Media.php`: コンストラクタ・`reconstruct`・`toArray` を `mediaId/title/url/publishedAt/type/isDisplay` の6フィールド構成に(format/platform/thumbnail 撤去)
- `Domain/Criteria/MediaSearchCriteria.php`: `Optional $format` プロパティ・`MediaFormat` use を撤去
- `Application/Admin/UseCase/Create/CreateUseCase.php`・`Update/UpdateUseCase.php`: `Media::create/reconstruct` 呼び出しから `formatValue`/`platformValue` 引数を撤去。InputData / 入力 DTO からも該当フィールドを除去
- `Application/Admin/UseCase/Search/SearchUseCase.php`: format 検索条件の組み立てを撤去
- `Infrastructures/MediaRepository.php`: `COLUMNS` と insert の列リスト・`values`・`ON DUPLICATE KEY UPDATE` 句から `format`/`platform`/`thumbnail_url` を撤去、`where('format', ...)` を撤去、`hydrate`(`reconstruct` 呼び出し)から `Row::int($row,'format')`/`Row::int($row,'platform')`/`Row::nullableString($row,'thumbnail_url')` を撤去
- `Infrastructures/Viewer/MediaQueryService.php` と `Application/Viewer/Query/MediaListItem.php`(および admin の Detail/List 用 Query DTO): 該当列の select/プロパティを撤去
- `Domain/Services/MediaIntegrityService.php`: (platform, thumbnail) 整合ルールがあれば撤去
- `Http/Presenters/Api/{Admin,Viewer}/V1/Media/*`(Converter / ListPresenter): 旧フィールドのマッピングを撤去
- Song 連携の波及: `packages/Song/Application/Admin/Assemble/AssembledMedia.php`・`SongAssembler.php`・`Application/Viewer/Query/SongMediaSummary.php`・`Infrastructures/Viewer/SongQueryService.php`・`Http/Presenters/.../Song/*` の Media 参照から旧フィールドを撤去
- **Other フォールバック**: `type` は必須。不正・未知の type 値が来た場合に `MediaType::Other` へ倒すフォールバックを Create/Update の入力変換または `Media::reconstruct` 周辺へ実装し、ユニットテストで担保する(現状 `MediaType::from()` は未知値で例外。`tryFrom() ?? MediaType::Other` 等へ)

依存: Step 2 完了必須(`Generated/*` の Model に依存)

### 4. DB スキーマを縮約しマイグレーションを適用する ✅

`src/server/database/atlas/schemas/media.my.hcl` を編集(declarative HCL。本リポジトリは `atlas schema apply` 方式で、版管理マイグレーションファイルは無い)

- `column "format"` / `column "platform"` / `column "thumbnail_url"` を削除
- `check "media_youtube_thumbnail"`(`expr = "(platform = 1) = (thumbnail_url IS NOT NULL)"`)を削除

検証/適用: `mise run migrate:dry-run`(差分が「3列 + CHECK の DROP」のみであることを確認)→ `mise run migrate`(local + testing へ適用)。既存 media データの移行は不要(再投入前提)

依存: Step 3 と整合(ドメインの toArray/hydrate が列構成と一致すること)

### 5. viewer を url 導出へ集約する ✅

- `src/viewer/src/features/media/labels.ts`: 既存 `youtubeThumbnailSrcset` を土台に、url から (a) host→プラットフォーム判定(YouTube / X / Instagram / その他=blog 等、`PostCard.astro` の `platformMeta` キーに対応するバッジ種別を返す)、(b) YouTube は動画 ID 抽出→サムネ URL 生成 + srcset を返すヘルパーを追加する。host 判定とサムネ生成をこのファイルに集約し、コンポーネントは url のみ渡す
- `src/viewer/src/components/viewer/PostCard.astro`: `entry.platform` / `entry.thumbnailUrl` 参照を `entry.url` からの導出(上記ヘルパー)へ置換。Props 型からも `platform` / `thumbnailUrl` を削除。サムネ無し(非 YouTube)でもレイアウトが破綻しないフォールバック表示を保つ
- `src/viewer/src/components/viewer/details/MediaDetailContent.astro`: `media.platform.name` / `media.thumbnailUrl` / `media.format.name` 参照を url 導出ヘルパー + `media.type.name` へ置換。プレースホルダのプラットフォーム表示も導出値に
- 必要に応じて `features/media/types.ts` の型から削除済みフィールドを除去

検証: `mise run viewer:check`

依存: Step 2 完了必須(`viewer/src/generated` の型に依存)

### 6. admin から platform/サムネ/format UI を撤去する ✅

- `src/admin/src/components/media/CreateForm.tsx`: `MEDIA_FORMAT_OPTIONS` / `MEDIA_PLATFORM_OPTIONS` 定義、`formatValue` / `platformValue` の `formData` 取得と送信ペイロード、対応する `<select>` と `getFieldError('formatValue'|'platformValue')` 表示を撤去。import の `MediaFormatValue` / `MediaPlatformValue` も削除
- `src/admin/src/components/media/EditableForm.tsx`: 同上に加え、`props.data.media.format` / `platform` / `thumbnailUrl`(サムネ `<img>` プレビュー含む)の参照を撤去
- `src/admin/src/components/media/SearchList.tsx`: `MEDIA_FORMAT_OPTIONS`・`format` シグナル/パラメータ・検索クエリへの `format` 連動を撤去
- `src/admin/src/server/routes/media.ts`: 検索/作成/更新で `format` / `platform` を中継している箇所があれば撤去

検証: `mise run admin:check`

依存: Step 2 完了必須(`admin/src/generated` の型に依存)

### 7. テストを更新し全体を検証する ✅

`src/server/tests/` の Media / Song 波及テストを 6 フィールド構成へ更新する

- Feature: `tests/Feature/Api/Admin/V1/Media/{Create,Update,Get,Search,Delete}MediaTest.php`、`tests/Feature/Api/Viewer/V1/Media/ListMediaTest.php`、`tests/Feature/Api/Admin/V1/Song/{Create,Get,Update}SongTest.php`、`tests/Feature/Api/Viewer/V1/Song/ListSongTest.php`。リクエスト/レスポンス JSON から `formatValue`/`platformValue`/`format`/`platform`/`thumbnailUrl` を除去、`type` を新値に
- Integration: `tests/Integration/Media/Application/Admin/UseCase/{Create,Update,Delete}UseCaseTest.php`
- Unit: `tests/Unit/Media/Domain/Services/MediaIntegrityServiceTest.php`。MediaType の新6値 + Other フォールバックのユニットテストを追加(`MediaType.php` の `getName()` と未知値→Other の確認)

検証: `mise run api:phpstan` → `mise run api:test`(必要に応じて `mise run api:arkitect`・`mise run api:ecs`)

依存: Step 3-4 完了必須

## Decision Log

- 2026-06-30: 変更の起点を契約(TypeSpec)に固定し、`mise run generate` で server/admin/viewer の生成物を更新する依存順(契約 → generate → 実装層)を Steps に反映。理由: コントラクト層を Source of Truth とし実装層を再生成可能に保つプロジェクト方針に合わせるため
- 2026-06-30: platform バッジ・サムネイルは url から viewer のみが導出し、ドメイン/DB/契約には保存しない。host 判定とサムネ生成は `features/media/labels.ts` に集約。理由: platform/サムネは表示責務であり状態ではない(軸の重複解消が本リファクタの目的)。admin は platform/サムネに非関与とし二重持ちを排除
- 2026-06-30: DB は declarative atlas(`atlas schema apply`、`mise run migrate*`)方式で版管理マイグレーションファイルが無いため、`media.my.hcl` を直接編集し `migrate:dry-run` で差分確認後 `migrate` で適用する手順とした。既存 media データは再投入前提で backfill しない(MEMORY: 既存データの互換性は気にしない)
- 2026-06-30: MediaType は必須・Other フォールバック。未知/不正 type を例外でなく `MediaType::Other` に倒す(`from()`→`tryFrom() ?? Other` 系)方針とし、ユニットテストで担保。理由: 旧 type+format 統合に伴う未知値混入時も投入を止めないため
- 2026-06-30: 作業ツリーの既存下書きは部分的(契約 `MediaTypeValue` 新値化・viewer/admin フロントが未対応)のため、本計画は完成状態を起点に全工程を記述し、下書きの未完・不整合を Step 1/5/6 で明示的に是正する
- 2026-06-30(実装時): 「現状メモ」が完了済みとした **server 側 Step 3 が実際には未完だった**。以下を追加で是正: `Infrastructures/Viewer/MediaQueryService.php` が削除済み列 `format/platform/thumbnail_url` を `withSelect` していた(実行時 SQL エラー)、`CreateInputData`/`UpdateInputData`/`SearchInputData`/`UpdateMediaController` に `formatValue`/`platformValue`/`$format` が残存、`packages/Song/Infrastructures/Viewer/SongQueryService.php::loadMedia` が `media.format`/`media.platform` を select(viewer build 時の API 500 で発覚)。いずれも撤去
- 2026-06-30(実装時): Other フォールバックは `MediaIntegrityService::toMediaType()` に実装。下書きは未知 type で `EntityRuleViolationError` を返していたが、合意設計どおり `MediaType::tryFrom($v) ?? MediaType::Other` へ変更(投入を止めない)。`MediaIntegrityServiceTest` に未知値→Other のテスト、`MediaTypeTest` に getName/tryFrom フォールバックのテストを追加。副次的に `UpdateMediaTest::emptyParameters`(typeValue=0)はエラー件数1(title のみ)で整合
- 2026-06-30(実装時): `PostCard.astro` は契約型ではなく **mock(`site-data.ts`) 駆動**で `url` を持たないため、url 導出は適用不可。契約フィールド由来の dead な `thumbnailUrl` Props と描画分岐のみ撤去し、mock の `platform` フィールドは温存。url 由来のサムネ/バッジ導出は契約データを使う `MediaDetailContent.astro` にのみ適用した
- 2026-06-30(レビュー): 指摘は Critical/Major なし。Minor 2件のうち、(1) `Media::reconstruct()` の `from()`(DB 復元経路は未知値で例外、入力経路の `tryFrom() ?? Other` と非対称)は「DB には常に正しい値が入る」前提でユーザー判断により**現状維持**。(2) `features/media/labels.ts` の `mediaPlatformFromUrl` は内部利用のみのため **export を撤去**し内部関数化(`viewer:check` 通過・外部参照なしを確認)
- 2026-06-30(実装時): admin の `MEDIA_TYPE_OPTIONS`(CreateForm/EditableForm/SearchList/MediaSection)のラベルが旧 type 値(動画/記事/SNS投稿/公式ページ)のままだったため、新6値(MV/音源動画/ライブ・配信/ショート/投稿/その他)へ更新。Step 6 に明記されていなかった `components/song/MediaSection.tsx`(楽曲のメディア紐付け UI)も `format` 参照で破綻していたため撤去対象に追加

## Validation

Acceptance Criteria と検証手段の対応:

- **6 フィールド化(Domain/DB/契約)**: `packages/media/domain.tsp`・`viewer/admin media domain.tsp`・`Media.php`・`media.my.hcl` を目視確認し `mediaId/title/url/publishedAt/isDisplay/type` のみであること。`mise run contract:test` 通過
- **MediaType 新6値・必須・Other フォールバック**: `MediaType.php` と `packages/media/domain.tsp` の `MediaTypeValue` が `Mv=1/AudioVideo=2/LiveStream=3/Short=4/Post=5/Other=99`。Unit テスト(`MediaIntegrityServiceTest` 近傍 + MediaType テスト)で `getName()` と未知値→Other を確認 → `mise run api:test`
- **platform/format/thumbnail の完全削除**: `git grep -i -E 'MediaFormat|MediaPlatform|MediaThumbnail|thumbnail_url|formatValue|platformValue' src/` が契約・サーバー・DB で 0 件(viewer の url 導出ヘルパー内部表現を除く)
- **生成物に platform/format/thumbnailUrl が出現しない**: `mise run generate` 後、`git grep -i -E 'MediaFormat|MediaPlatform|thumbnailUrl' src/server/Generated src/admin/src/generated src/viewer/src/generated src/contracts/generated/oas` が 0 件
- **viewer: YouTube url → サムネ(srcset)+バッジ表示** / **他ホスト url → バッジ表示・サムネ無しでもレイアウト維持**: `mise run viewer:dev` で YouTube / X 等の url を持つ media を表示し目視確認。`mise run viewer:check` 通過
- **admin フォームに platform/サムネ/format が無い**: `CreateForm.tsx`・`EditableForm.tsx`・`SearchList.tsx` を確認し `mise run admin:dev` で目視。`mise run admin:check` 通過
- **総合**: `mise run contract:test`・`mise run api:phpstan`・`mise run api:test`・`mise run viewer:check`・`mise run admin:check` がすべて通過。DB は `mise run migrate:dry-run` の差分が「format/platform/thumbnail_url 列 + media_youtube_thumbnail CHECK の DROP」のみ
