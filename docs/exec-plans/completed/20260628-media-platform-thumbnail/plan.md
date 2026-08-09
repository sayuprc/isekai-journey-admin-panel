# Execution Plan — Plan

実装計画（どう進めるか）を書く。問題定義は同ディレクトリの `issue.md` を参照する。

## Title

Media にプラットフォーム種別とサムネイルを第一級で保持する

## Status

completed

## Steps

1. ✅ **Domain: 値オブジェクトと enum を追加する**
   - `src/server/packages/Media/Domain/Models/MediaPlatform.php`: `enum MediaPlatform: int`（`YouTube=1` / `X=2` / `Other=99`）。`MediaType`/`MediaFormat` と同形で `getName()` / `equals()` を実装。さらに `public static function fromUrl(MediaUrl $url): self` を追加し、`parse_url()` の host を小文字化して `youtube.com`（`www.` 等のサブドメイン含む）→ `YouTube`、`x.com` / `twitter.com` → `X`、それ以外（`youtu.be` 含む）→ `Other` を返す。
   - `src/server/packages/Media/Domain/Models/MediaThumbnail.php`: `StringValueObject` を継承する null 非許容の URL VO。`MediaUrl.php` と同じ最小実装にしつつ、YouTube 用の名前付きファクトリ `public static function fromYouTubeUrl(MediaUrl $url): Result<self, EntityRuleViolationError>` を追加。`watch?v=` クエリ（`parse_url` + `parse_str`）から video ID を抽出し、取れたら `new Ok(self::reconstruct("https://i.ytimg.com/vi/{id}/sddefault.jpg"))`、取れなければ `new Err(new EntityRuleViolationError('thumbnailUrl', 'YouTube の動画IDを取得できません'))` を返す（業務エラーは例外でなく Result）。

2. ✅ **Domain: `Media` に platform / thumbnail を持たせ不変条件を担保する**
   - `src/server/packages/Media/Domain/Models/Media.php`: コンストラクタに `public MediaPlatform $platform` と `public ?MediaThumbnail $thumbnail` を追加。コンストラクタは `private` 寄りにせず、不変条件「YouTube ⟺ thumbnail あり」は名前付きコンストラクタで担保する:
     - `public static function youtube(MediaId, MediaTitle, MediaUrl, MediaPublishedAt, MediaFormat, bool $isDisplay, MediaThumbnail $thumbnail): self`（platform=YouTube 固定、thumbnail 必須）
     - `public static function x(...): self` / `public static function other(...): self`（thumbnail を受け取らず内部で null・platform を固定）
   - `reconstruct()` に `int $platform` と `?string $thumbnailUrl` を引数追加し、`MediaPlatform::from()` / `is_null($thumbnailUrl) ? null : MediaThumbnail::reconstruct($thumbnailUrl)` で復元。
   - `toArray()` に `'platform' => $this->platform->value`、`'thumbnail_url' => $this->thumbnail?->value` を追加（PHPDoc も更新）。

3. ✅ **Domain Service: `MediaIntegrityService` を名前付きコンストラクタ経由に更新し platform を確定する**
   - `src/server/packages/Media/Domain/Services/MediaIntegrityService.php`: `prepareForCreate` / `prepareForUpdate` / `build` のシグネチャに `?int $platformValue`（任意。null なら URL 自動判定）を追加。
   - `build` 内で `MediaUrl::create($url)` 成功後、`platform = is_null($platformValue) ? MediaPlatform::fromUrl($url) : (MediaPlatform::tryFrom($platformValue) ?? Err)` を確定。`platform === YouTube` のとき `MediaThumbnail::fromYouTubeUrl($url)` を呼び、`Err` ならそのまま伝播。確定した platform に応じて `Media::youtube(...)` / `::x(...)` / `::other(...)` を呼び分ける。
   - `Result::collect7` は引数の都合で `collect8`（platform 検証結果を加える）等へ拡張するか、VO collect は従来どおりにして platform/thumbnail の解決は collect 後の `andThen` で行う（不正 `platformValue` は `EntityRuleViolationError('platformValue', ...)` を `Err` で返す）。

4. ✅ **Application: UseCase / InputData に `platformValue`（任意）を通す**
   - `src/server/packages/Media/Application/Admin/UseCase/Create/CreateInputData.php` と `Update/UpdateInputData.php`: `public ?int $platformValue = null` を追加（`MapperInterface` で `$request->all()` から束縛されるため末尾に nullable で追加）。
   - `Create/CreateUseCase.php` / `Update/UpdateUseCase.php`: `prepareForCreate` / `prepareForUpdate` の呼び出しに `$inputData->platformValue` を渡す。`Err`（`EntityRuleViolationError` / `DomainValidationError`）は既存 `handleError` 経由で `InvalidInputError`（Unprocessable）に変換され、UseCase は薄いまま。

5. ✅ **Infrastructure: 永続化と viewer 読み出しに platform / thumbnail_url を追加する**
   - `src/server/packages/Media/Infrastructures/MediaRepository.php`: `COLUMNS` に `'platform'`, `'thumbnail_url'` を追加。`save()` の `into(...)` カラム・`values([...])`（`$data['platform']`, `$data['thumbnail_url']`）・`ON DUPLICATE KEY UPDATE` 句に両カラムを追加。`hydrate()` で `Row::int($row, 'platform')`、`thumbnail_url` は NULL 許容なので `Row` の nullable 取得（無ければ `$row['thumbnail_url'] ?? null` を `is_null` 判定）で `Media::reconstruct(...)` に渡す。
   - `src/server/packages/Media/Application/Viewer/Query/MediaListItem.php`: `public MediaPlatform $platform` と `public ?string $thumbnailUrl` を追加。
   - `src/server/packages/Media/Infrastructures/Viewer/MediaQueryService.php`: `withSelect([...])` に `'platform'`, `'thumbnail_url'` を追加し、`new MediaListItem(...)` に `MediaPlatform::from(Row::int($mediaRow, 'platform'))` と thumbnail_url（nullable）を渡す（再導出せず保存値を信頼）。

6. ✅ **DB: atlas スキーマにカラムと CHECK 制約を追加しマイグレーションを適用する**
   - `src/server/database/atlas/schemas/media.my.hcl`: `column "platform" { null = false; type = tinyint; unsigned = true; comment = "プラットフォーム種別" }` と `column "thumbnail_url" { null = true; type = text; comment = "サムネイルURL" }` を追加。テーブル末尾に `check "media_youtube_thumbnail" { expr = "(platform = 1) = (thumbnail_url IS NOT NULL)" }` を追加（既存に CHECK 例は無いため atlas hcl の `check` ブロックを新規採用）。既存行は移行戦略なしで platform=99/Other 既定値が無いため、`mise run migrate` 前提だが本番ユーザー不在のため backfill しない（必要なら適用時にテーブル truncate/再投入）。
   - `mise run migrate`（`migrate:local` / `migrate:testing`）で適用し、`(platform=1, thumbnail_url=NULL)` 行が弾かれることを確認。

7. ✅ **契約: TypeSpec を更新し各クライアントを再生成する**
   - `src/contracts/src/packages/media/domain.tsp`: `MediaPlatformValue` enum（`YouTube:1` / `X:2` / `Other:99`）、`MediaPlatform { name: mediaPlatformName; value: MediaPlatformValue }` model、`mediaPlatformName` scalar、`@friendlyName("mediaThumbnailUrl") scalar mediaThumbnailUrl extends string`（`@format("uri")`）を `MediaType` / `MediaFormat` と同形で追加。
   - `src/contracts/src/admin/media/domain.tsp`: `Media` model に `platform: MediaPlatform;` と `thumbnailUrl?: mediaThumbnailUrl;` を追加。
   - `src/contracts/src/admin/media/transport.tsp`: `MediaCreateRequest` / `MediaUpdateRequest` に `platformValue?: MediaPlatformValue;` を追加。
   - `src/contracts/src/viewer/media/domain.tsp`: `MediaListItem` に `platform: MediaPlatform;` と `thumbnailUrl?: mediaThumbnailUrl;` を追加（`@example` も更新）。
   - `mise run contract:compile:admin` / `contract:compile:viewer` で OAS を再生成（`scripts/fix-enum-types.ts` が numeric enum を補正）。続けて `mise run api:generate` / `admin:generate` / `viewer:generate` で PHP / TS クライアントを再生成。

8. ✅ **Backend Presenter: 再生成された OpenAPI モデルへ platform / thumbnail をマッピングする**
   - `src/server/app/Http/Presenters/Api/Admin/V1/Media/Converter.php`: `toOpenApiMedia()` に `->setPlatform(new OpenApiMediaPlatform()->setName($media->platform->getName())->setValue(MediaPlatformValue::from($media->platform->value)))` と `->setThumbnailUrl($media->thumbnail?->value)` を追加（Get/Search/Create/Update 各 Presenter は本 Converter を共用するため一括反映）。
   - `src/server/app/Http/Presenters/Api/Viewer/V1/Media/ListPresenter.php`: `toOpenApiMediaListItem()` に platform（name/value）と thumbnailUrl のセットを追加。

9. ✅ **admin フロント: platform セレクト（空=自動判定）とサムネイル表示を追加する**
   - `src/admin/src/components/media/CreateForm.tsx` / `EditableForm.tsx`: `MEDIA_TYPE_OPTIONS` に倣い `MEDIA_PLATFORM_OPTIONS`（空 option=自動判定 + YouTube/X/Other）を定義。種別/形式の `grid` 内に platform `<select name="platformValue">` を追加し、`post`/`put` ペイロードに `platformValue: formData.get('platformValue') ? Number(...) : undefined` を含める（型は再生成された `MediaPlatformValue`）。EditableForm は `data.media.thumbnailUrl` があれば読み取り専用の `<img>` を表示（編集不可）。

10. ✅ **viewer フロント: プラットフォームバッジとサムネイルを表示する**
    - `src/viewer/src/features/media/types.ts`: 再生成された `MediaListItem` 由来で `platform` / `thumbnailUrl` が型に乗ることを確認（`Media` 型は generated を再エクスポートのため自動反映）。
    - `src/viewer/src/features/media/labels.ts`: `MediaPlatformValue`（1/2/99）→ バッジ表示用ラベル/マークの対応と、YouTube サムネ用 `srcset` を生成するヘルパ（保存 URL の `sddefault.jpg` を `hqdefault.jpg` / `mqdefault.jpg` 等にファイル名差し替え、`maxres` は使わない）を追加。
    - `src/viewer/src/components/viewer/PostCard.astro`: `entry.platform` を保存値ベースに合わせ、`platformMeta` を MediaPlatform に整合させてバッジ表示。`thumbnailUrl` があるとき（YouTube のみ非 null）`<img>`（`srcset` ヘルパ使用）を表示し、再導出せず保存値を使う。`MediaDetailContent.astro` 等で同データを使う箇所も合わせて確認。

## Decision Log

- 2026-06-28: 不変条件「YouTube ⟺ thumbnail あり」は `Media` の名前付きコンストラクタ（`youtube()` / `x()` / `other()`）で担保し、`x()`/`other()` は thumbnail 引数を受け取らないことで型レベルで誤生成を防ぐ（issue 合意済み設計）。
- 2026-06-28: サムネイル URL 生成失敗（YouTube だが video ID 取得不可）は例外でなく `ResultType\Err`（`EntityRuleViolationError`）で返し、UseCase の `handleError` 経由で Unprocessable にする（プロジェクト規約: 業務エラーは Result）。
- 2026-06-28: `platformValue` は契約・InputData ともに任意。未指定時は `MediaPlatform::fromUrl` で URL から自動判定、指定時は値を優先（不正値は Err）。
- 2026-06-28: DB は CHECK 制約 `(platform = 1) = (thumbnail_url IS NOT NULL)` を atlas hcl の `check` ブロックで新規導入（既存テーブルに CHECK 例なし）。既存データ backfill は行わない（本番ユーザー不在）。
- 2026-06-28: viewer はサムネイルを再導出せず保存値を表示。srcset の解像度バリエーションのみフロントのヘルパでファイル名差し替えにより生成し、`maxres` は使わない。
- 2026-06-28（実装時追記）: 名前付きコンストラクタ `Media::youtube()` / `::x()` / `::other()` は plan の略記と異なり `MediaType $type` も受け取る。`type` は platform と直交する必須項目で削れないため全 named constructor に残した（Non-Scope の format⊂Video 是正には踏み込まない）。
- 2026-06-28（実装時追記）: 契約の `thumbnailUrl?`（optional・非 nullable）に対し openapi-generator 製 PHP モデルの `setThumbnailUrl(null)` は例外を投げるため、`Media` Converter / viewer `ListPresenter` / Song `Converter` では `thumbnail` が非 null のときだけ setter を呼ぶ（Other/X は thumbnailUrl をレスポンスに含めない）。
- 2026-06-28（実装時追記・スコープ波及）: admin の `SongLinkedMedia is Media` が必須 `platform` を継承するため、Song 読み出し経路（`Application/Admin/Assemble/AssembledMedia` / `SongAssembler` / admin Song `Converter`）と契約例（`admin/songs/domain.tsp`）・関連テストにも platform/thumbnail を伝播させた。Media ドメイン値をそのまま流すだけで再導出はしない。
- 2026-06-28（実装時追記）: migration 適用で既存 local `media` 2 行が暗黙の `platform=0`（enum 不正値）になり viewer build（ビルド時に live local API を fetch）が 500 になった。plan の「必要なら truncate/再投入」方針に従い、local 限定でこの 2 行を `platform=99`（Other）へ更新して整合させた（testing DB の行はテストごとに管理されるため対象外）。backfill ロジックはコードに入れていない。
- 2026-06-28（レビュー後修正）: ドロワーのサムネが見切れる不具合。`.media-detail-thumbnail` に CSS が無く img が固有サイズ（sddefault 640×480）で溢れていた。`src/viewer/src/styles/viewer.css` に `.media-detail-thumbnail`（`width:100%` / `aspect-ratio:16/9` / `object-fit:cover` / `border`）を追加し、`sddefault`/`hqdefault` の 4:3 黒帯もトリミング。ドロワー内幅は当初 `.mv-thumb` と揃え 280px としたが、サムネは主役ビジュアルのためドロワー専用の幅制約を撤廃し、基底の `width:100%`（コンテンツ幅いっぱい・約570px）で表示。`viewer:check` / `viewer build` 通過。
- 2026-06-28（レビュー後修正）: プラットフォームの記号バッジ（▶/𝕏/¶）はドロワー詳細から削除し、メタ行の `media.platform.name` テキストのみでプラットフォームを示すことにした（弱い記号より名前テキストが明快というユーザー判断）。`MediaDetailContent.astro` のバッジ span・関連 import/変数を除去し、未使用化した `labels.ts` の `mediaPlatformBadge`/`MEDIA_PLATFORM_BADGES`/`MediaPlatformBadge` 型もデッドコードとして削除。PostCard（モック）の独自バッジは別系統のため未変更。`viewer:check` / `viewer build` 通過。
- 2026-06-28（レビュー後修正）: ドロワーの「開く」ボタンを廃し、サムネイルクリックで外部ページを別タブ表示する導線に変更。`MediaDetailContent.astro` でサムネ `<img>` を `<a href={media.url} target="_blank" rel="noopener noreferrer">` で包み、`.media-detail-thumbnail-link` にホバー不透明化を付与。サムネの無い X/Other は開く導線が消えないよう、従来の「開く」ボタンを `!media.thumbnailUrl` のフォールバックとして残した（YouTube ではボタン非表示）。`viewer:check` / `viewer build` 通過。
- 2026-06-28（レビュー後修正）: サムネがリンクであることを明示するため、ホバー/フォーカス時に暗転オーバーレイ+「{platform名} で開く ↗」を中央表示する `.media-detail-thumbnail-overlay` を追加（`.label-mono` と同じ JetBrains Mono、白文字+`rgba(0,0,0,0.5)`）。`:focus-visible` にも対応。`viewer:check` / `viewer build` 通過。
- 2026-06-29（レビュー後追加）: 楽曲詳細の関連メディア表示を「公開日 · Format」→「公開日 · Platform · Format」に変更。viewer の `SongMediaSummary` に platform が無かったため縦断追加: 契約 `viewer/songs/domain.tsp`(+`@example`)・`transport.tsp`、`Song/Application/Viewer/Query/SongMediaSummary.php`、`Song/Infrastructures/Viewer/SongQueryService.php`(`media.platform` を select、保存値を信頼)、viewer `Song/ListPresenter.php`(platform 必須のため無条件 setter)、`SongDetailContent.astro:89`、`ListSongTest.php` 期待 JSON。再生成含め契約/`api:test`(509件)/`viewer:check`/`viewer build` 通過。
- 2026-06-29（レビュー後修正）: サムネの無い X/Other のドロワー表示が未考慮だった。YouTube だけ画像・他はボタンのみという非対称を解消するため、ヒーロー枠を常にクリック可能なリンクとして描画し、サムネ有り→画像 / 無し→同サイズ(16:9)のプレースホルダ（アクセント色グラデ+中央にプラットフォーム名）を出し分ける形に統一。ホバー導線オーバーレイは共通、別枠の「開く」ボタンは廃止（ヒーロークリックに一本化）。`MediaDetailContent.astro` / `viewer.css`（`.media-detail-thumbnail-placeholder*`）。`viewer:check` / `viewer build` 通過。

## Validation

- バックエンド: `mise run api:ecs` / `mise run api:phpstan` / `mise run api:arkitect` / `mise run api:test`
- 契約: `mise run contract:format:check` / `mise run contract:test` / `mise run contract:compile:admin` / `mise run contract:compile:viewer`
- クライアント再生成: `mise run api:generate` / `mise run admin:generate` / `mise run viewer:generate`（生成物に platform / thumbnailUrl が反映されること）
- admin フロント: `cd src && bun --filter admin lint:check` / `bun --filter admin style:check` / `bun --filter admin build`
- viewer フロント: `mise run viewer:check` / `cd src && bun --filter viewer build`
- DB: `mise run migrate`（local / testing）適用後、`(platform=1, thumbnail_url=NULL)` および `(platform≠1, thumbnail_url NOT NULL)` の行が CHECK 制約で挿入拒否されることを確認
