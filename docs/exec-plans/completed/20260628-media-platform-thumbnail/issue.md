# Execution Plan — Issue

## Title

Media にプラットフォーム種別とサムネイルを第一級で保持する

## Background

現在の `Media`(`src/server/packages/Media/Domain/Models/Media.php`)は `type` / `format` は持つが、リンク先がどのプラットフォーム(YouTube / X / その他)かを構造として保持していない。そのため viewer 側でリンク先の区別やサムネイル表示ができず、URL から都度推測するしかない

YouTube のメディアについては動画サムネイルを表示したいが、サムネイル URL を保持する場所がない。プラットフォーム種別とサムネイルをドメインの第一級の概念として持たせ、保存値を信頼して viewer で表現できるようにする

## Goal

`Media` にプラットフォーム種別(YouTube / X / Other)を必須で、YouTube のときはサムネイル URL を保持させる。viewer でプラットフォームバッジとサムネイル表示に使えるよう、Domain から契約・永続化・フロントまで一貫して platform / thumbnailUrl を流す

## Scope

### Domain(`src/server/packages/Media/Domain/`)
- `Models/MediaPlatform.php`: enum(YouTube=1 / X=2 / Other=99、必須、判別不能は Other)。`fromUrl(MediaUrl): self` を host 基準で実装(youtube.com→YouTube / x.com・twitter.com→X / 他→Other、youtu.be は当面 Other)
- `Models/MediaThumbnail.php`: URL 文字列の VO(null 許容、X/Other は null)。watch?v= からの video ID 抽出とサムネ URL(`https://i.ytimg.com/vi/{id}/sddefault.jpg`)生成を担う。YouTube なのに video ID 取得不可は `Err`(業務エラーは例外でなく `ResultType\Ok` / `Err`)
- `Models/Media.php`: `platform`(必須)+ `?thumbnail` を追加。名前付きコンストラクタ `Media::youtube(..., MediaThumbnail)` / `::x()` / `::other()` で不変条件「YouTube ⟺ thumbnail あり」を担保。`reconstruct` / `toArray` も platform・thumbnail_url に対応
- 既存のファクトリ責務を持つ `Domain/Services/MediaIntegrityService` を上記名前付きコンストラクタ経由に更新

### DB(`src/server/database/atlas/schemas/media.my.hcl`)
- `platform` tinyint unsigned NOT NULL を追加
- `thumbnail_url` text NULL を追加
- CHECK 制約 `(platform = 1) = (thumbnail_url IS NOT NULL)` を追加
- 既存行は platform=Other / thumbnail_url=NULL で通す(backfill しない)

### 契約(`src/contracts/`)
- `packages/media/domain.tsp`: `MediaPlatformValue` enum(YouTube=1 / X=2 / Other=99)+ `MediaPlatform { name, value }` model + `mediaThumbnailUrl` scalar を追加
- `viewer/media/domain.tsp`: `MediaListItem` に `platform` + `thumbnailUrl?` を追加
- `admin/media/domain.tsp`: `Media` に `platform` + `thumbnailUrl?` を追加
- `admin/media/transport.tsp`: `MediaCreateRequest` / `MediaUpdateRequest` に `platformValue?`(任意、未指定なら自動判定)を追加

### Application / Infrastructure(`src/server/packages/Media/`)
- `Application/Admin/UseCase/Create` / `Update`: `platformValue` 指定があれば優先、無ければ `MediaPlatform::fromUrl` で確定 → ファクトリ呼び出し → `Err` は Unprocessable で返す(薄い UseCase に保つ)
- `Infrastructures/MediaRepository.php`: `COLUMNS` / insert カラム / `ON DUPLICATE KEY UPDATE` / `hydrate` に platform・thumbnail_url を追加
- viewer 読み出し(`Application/Viewer/Query`)は再導出せず保存値を信頼

### フロント
- admin(`src/admin/src/components/media/CreateForm.tsx` / `EditableForm.tsx`): platform セレクト(空=自動判定)を追加、サムネイルは表示専用
- viewer(`src/viewer/src/components/viewer/PostCard.astro`、`src/viewer/src/features/media/`): platform バッジ + サムネイル `<img>` を表示。srcset はフロントのヘルパで YouTube のファイル名差し替えにより生成(maxres は使わない)

## Non-Scope

- YouTube 一括 import の実装(別 PR で artisan コマンド化)
- `format` を `type=Video` 条件付きにする是正(次タスク)
- 既存データの backfill(platform=Other / thumbnail_url=NULL のまま通す)

## Acceptance Criteria

- `Media::youtube()` を thumbnail なしで生成しようとすると不変条件で弾かれ、`::x()` / `::other()` に thumbnail を渡せない(型で担保される)
- `MediaPlatform::fromUrl` が youtube.com を YouTube、x.com / twitter.com を X、その他(youtu.be 含む)を Other に判別する
- YouTube の watch?v= URL から video ID を抽出し `https://i.ytimg.com/vi/{id}/sddefault.jpg` を生成する。video ID を抽出できない YouTube URL は `Err` を返し保存されない
- DB に platform NOT NULL / thumbnail_url NULL / CHECK 制約 `(platform = 1) = (thumbnail_url IS NOT NULL)` が定義され、platform=1 かつ thumbnail_url が NULL の行(およびその逆)が挿入できない
- admin Create/Update で platformValue 未指定のとき URL から自動判定され、指定時はその値が優先される
- admin Create/Update が保存した platform・thumbnail_url を MediaRepository が永続化し、再取得時に hydrate で復元される
- viewer の `MediaListItem` が platform と thumbnailUrl?(YouTube のみ非 null)を返し、PostCard にプラットフォームバッジと(YouTube のとき)サムネイル画像が表示される
- viewer はサムネイルを再導出せず保存値を表示する
- `mise run api:ecs` / `api:phpstan` / `api:arkitect` / `api:test` が通る
