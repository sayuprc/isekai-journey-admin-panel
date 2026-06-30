# Execution Plan — Issue

## Title

Media モデルを「素の状態＋分類1軸」へ作り直す（#835 platform/thumbnail 巻き戻し）

## Background

現在の Media モデルは type / format / platform の 3 分類軸が重複しており、SocialPost(type) と X(platform) のような二重持ちや、「X の動画ツイート」が表現不能などの歪みを抱えている。

直近の #835（commit 538540fe）で platform / thumbnail を追加したが、これがさらに軸の重複を強めた。platform とサムネイルは本来 url から導出できる viewer の表示責務であり、ドメイン・DB・契約に保存すべき状態ではない。

そこで Media を 6 フィールドの素の状態に縮約し、分類を MediaType の 1 軸へ統合する。

## Goal

Media の状態を Domain / DB / 契約で共通の 6 フィールド（mediaId / title / url / publishedAt / isDisplay / type）へ単純化する。旧 type+format を統合した唯一の分類軸 MediaType を定義し、#835 で追加した platform / thumbnail をモデルから巻き戻す。platform バッジとサムネイルは viewer が url から導出する。

## Scope

### 確定する Media 状態（6 フィールド）

- mediaId / title / url(URI, 一意制約なし＝現状維持) / publishedAt(date) / isDisplay(bool) / type(MediaType, 必須)

### MediaType（旧 type + 旧 format を統合した唯一の分類軸・必須・Other フォールバック）

- Mv=1 / AudioVideo=2 / LiveStream=3 / Short=4 / Post=5 / Other=99
- 用途は表示ラベル＋将来フィルタのみ。API 業務ロジックには非関与
- 「オリジナル / 歌ってみた(カバー)」は Song.type の責務なので Media には持たせない

### 1. 契約（TypeSpec）— 起点。編集後 `mise run generate`

- `src/contracts/src/packages/media/domain.tsp`: MediaTypeValue を上記 6 値へ再定義。旧 MediaType 値・MediaFormat(Value)・MediaPlatform(Value)・mediaThumbnailUrl を削除
- `src/contracts/src/viewer/media/domain.tsp`: `format` / `platform` / `thumbnailUrl` フィールドと example を削除
- `src/contracts/src/viewer/media/transport.tsp`: 同上の example 削除
- `src/contracts/src/admin/media/domain.tsp`: `format` / `platform` / `thumbnailUrl` 削除
- `src/contracts/src/admin/media/transport.tsp`: Create/Update の `formatValue` / `platformValue` 削除
- `src/contracts/src/admin/media/service.tsp`: 検索クエリ `format?: MediaFormatValue` 削除

### 2. サーバー（`src/server/packages/Media`）

- 削除: `Domain/Models/MediaPlatform.php`, `Domain/Models/MediaFormat.php`, `Domain/Models/MediaThumbnail.php`
- `Domain/Models/MediaType.php`: 6 値へ再定義
- `Domain/Models/Media.php`: format / platform / thumbnail を撤去し 6 フィールド構成へ
- `Domain/Criteria/MediaSearchCriteria.php`, `Application/Admin/UseCase/Search/*`: format 検索条件を撤去
- `Infrastructures/MediaRepository.php`: format / platform / thumbnail_url 列の読み書きを撤去
- `Application/Admin/UseCase/Create/*`, `Update/*`: platform / thumbnail / format 処理を削除
- `Infrastructures/Admin/MediaDetailQueryService.php`, `Infrastructures/Viewer/MediaQueryService.php`, 各 Query DTO（`MediaListItem` 等）: 該当列の参照を撤去
- `Domain/Services/MediaIntegrityService.php`: (platform, thumbnail) 整合ルールがあれば撤去

### 3. DB スキーマ

- `src/server/database/atlas/schemas/media.my.hcl`: `format` 列・`platform` 列・`thumbnail_url` 列・`check "media_youtube_thumbnail"`（`(platform = 1) = (thumbnail_url IS NOT NULL)`）を削除

### 4. viewer（`src/viewer/src`）— url 導出に集約

- `features/media/labels.ts`: url から host→プラットフォーム判定、YouTube 動画 ID→サムネ URL+srcset を導出するヘルパーへ拡張（既存 `youtubeThumbnailSrcset` の延長）
- `components/viewer/PostCard.astro`, `components/viewer/details/MediaDetailContent.astro`: 契約の platform/thumbnailUrl 参照を url 導出ヘルパー呼び出しへ置換

### 5. admin（`src/admin/src`）

- `components/media/CreateForm.tsx`, `components/media/EditableForm.tsx`: platform セレクト・サムネ表示・format 入力を撤去
- `components/media/SearchList.tsx`, `server/routes/media.ts`: format/platform 連動箇所があれば撤去

## Non-Scope

- 既存 media データの backfill / 移行（既存 media は再投入する前提。意味保持の移行は不要）
- イベント連携（Media とイベントの関連付け）
- Song との関連（`song_media_links` 経由は現状維持。変更しない）
- url の一意制約追加（現状どおり制約なし）
- 将来フィルタ機能そのものの実装（MediaType はラベル用途のみ先行整備）

## Acceptance Criteria

- Media の状態が Domain / DB / 契約のいずれでも mediaId / title / url / publishedAt / isDisplay / type の 6 フィールドのみになっている
- MediaType が Mv=1 / AudioVideo=2 / LiveStream=3 / Short=4 / Post=5 / Other=99 で定義され、type が必須・Other フォールバックを持つ
- 契約・サーバー・DB から MediaPlatform / MediaFormat / MediaThumbnail（enum / VO / 列 / CHECK / contract 型 / Create・Update フィールド / 検索クエリ）が完全に消えている
- `mise run generate` 後、生成物（OAS・各クライアント型）に platform / format / thumbnailUrl が出現しない
- viewer で YouTube の url を持つ media を表示したとき、url から導出したサムネイル（srcset 付き）とプラットフォームバッジが表示される
- viewer で X など他ホストの url を持つ media を表示したとき、url 由来のプラットフォームバッジが表示され、サムネイルが無くてもレイアウトが破綻しない
- admin の作成・編集フォームに platform セレクト・サムネ表示・format 入力が存在しない
- `mise run contract:test`, `mise run api:phpstan`, `mise run api:test`, `mise run viewer:check`, `mise run admin:check` 相当が通る
