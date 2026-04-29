# Title

楽曲タグ編集機能の実装

## Status

completed

## Background

`src/contracts/src/admin/song-tags/service.tsp` には `updateSongTag` 契約が既に定義されているが、サーバー側の実装（UpdateSongTagController、UpdatePresenter、Update UseCase）がない。また、admin BFF（`src/admin/src/server/routes/song-tags.ts`）にも update エンドポイントがなく、管理画面に楽曲タグを編集する UI（詳細ページ、EditableForm）がない。さらに、タグの詳細取得 API（`getSongTag` / `showSongTag`）も contracts で未定義のため、既存の `searchSongTags` と `listSongTags` からは個別データを取得できない。

## Goal

管理画面から楽曲タグを編集できるようにし、API 契約と BFF ルートをつなぎ、UI に編集フォームを追加する。タグ詳細取得 API も定義し、編集画面でタグデータを読み込めるようにする。

## Scope

- `src/contracts/src/admin/song-tags/service.tsp` に `getSongTag` API 操作を追加し、`SongTagGetResponse` を定義する
- `src/server/packages/Song/Application/UseCase/Tag/Get` に、タグ詳細取得の use case を追加する
- `src/server/app/Http/Controllers/Api/SongTag/GetSongTagController.php`、`src/server/app/Http/Presenters/Api/SongTag/GetPresenter.php` を追加する
- `src/server/routes/admin.php` に `GET /admin/v1/song-tags/{songTagId}` ルートを追加する
- `src/admin/src/server/routes/song-tags.ts` に GET（詳細取得）と PUT（更新）エンドポイントを追加する
- `src/server/packages/Song/Application/UseCase/Tag/Update` に、タグ更新の use case を追加する
- `src/server/app/Http/Controllers/Api/SongTag/UpdateSongTagController.php`、`src/server/app/Http/Presenters/Api/SongTag/UpdatePresenter.php` を追加する
- `src/server/routes/admin.php` に `PUT /admin/v1/song-tags/{songTagId}` ルートを追加する
- `src/admin/src/pages/song-tags/[id].astro` を追加し、タグ詳細ページとしてデータを読み込む
- `src/admin/src/components/song-tag/EditableForm.tsx` を追加し、performer の EditableForm.tsx を参考に名前と表示順の編集機能を実装する
- `src/admin/src/components/song-tag/SearchList.tsx` の行をクリッカブルにして詳細ページへ遷移できるようにする

## Non-Scope

- `src/contracts/src/admin/song-tags` の既存契約（`createSongTag`、`listSongTags`、`searchSongTags`）の変更
- `src/server/app/Models/Song/SongTag.php` の re-design
- DB スキーマ変更やマイグレーション追加
- 削除機能（Delete）の実装
- soft delete や論理削除の実装

## Acceptance Criteria

- `src/contracts/src/admin/song-tags/service.tsp` に `getSongTag` API が定義され、`SongTagGetResponse` が存在する
- 管理者が `GET /admin/v1/song-tags/{songTagId}` を送ると、指定したタグが返される（存在しない場合は 404）
- 管理者が `PUT /admin/v1/song-tags/{songTagId}` を送ると、名前と表示順が更新され、成功レスポンスまたは入力エラーが返される
- `src/admin/src/pages/song-tags/[id].astro` にアクセスすると、タグの詳細データが読み込まれ、EditableForm で編集できる
- SearchList のタグ行をクリックすると `/song-tags/[id]` へ遷移する
- 更新成功時に `/song-tags[back_query]` へリダイレクトし、flash message で「更新しました」と表示される

## Steps

1. **contracts: `getSongTag` API と `SongTagGetResponse` を追加する**
   - `src/contracts/src/admin/song-tags/transport.tsp` に `model SongTagGetResponse { tag: SongTag; }` を追加する
   - `src/contracts/src/admin/song-tags/service.tsp` に `getSongTag` 操作を追加する
   - `mise run contract:compile:admin` → `mise run generate:client:admin` を実行してクライアントを再生成する

2. **サーバー: `SongTagRepositoryInterface` に `find` を追加し実装する**
   - `src/server/packages/Song/Domain/Models/Tag/SongTagRepositoryInterface.php` に `find(SongTagId $songTagId): ?SongTag` を追加する
   - `src/server/packages/Song/Infrastructures/Tag/SongTagRepository.php` に `find` の実装を追加する（`PerformerRepository::find` と同パターン）

3. **サーバー: `SongTagIntegrityService` に `prepareForUpdate` を追加する**
   - `src/server/packages/Song/Domain/Services/SongTagIntegrityService.php` に `prepareForUpdate(string $songTagId, string $name, int $orderNo): Result<SongTag, DomainError>` を追加する（`PerformerIntegrityService::prepareForUpdate` と同パターン）
   - 依存: ステップ 2

4. **サーバー: Get / Update の UseCase・Interactor を追加する**
   - `src/server/packages/Song/Application/UseCase/Tag/Get/` に `GetInputData.php`・`GetOutputData.php`・`GetUseCaseInterface.php` を作成する
   - `src/server/packages/Song/Application/Interactors/Tag/GetInteractor.php` を作成する（`Permission::ReadSong`、NotFound ケースを含む）
   - `src/server/packages/Song/Application/UseCase/Tag/Update/` に `UpdateInputData.php`・`UpdateOutputData.php`・`UpdateUseCaseInterface.php` を作成する
   - `src/server/packages/Song/Application/Interactors/Tag/UpdateInteractor.php` を作成する（`Permission::WriteSong`）
   - 依存: ステップ 2, 3

5. **サーバー: Controller・Presenter・ルート・ServiceProvider を追加する**
   - `src/server/app/Http/Controllers/Api/SongTag/GetSongTagController.php` を作成する
   - `src/server/app/Http/Presenters/Api/SongTag/GetPresenter.php` を作成する
   - `src/server/app/Http/Controllers/Api/SongTag/UpdateSongTagController.php` を作成する
   - `src/server/app/Http/Presenters/Api/SongTag/UpdatePresenter.php` を作成する
   - `src/server/packages/Song/Route/Tag/SongTagRouteMap.php` に `Get` ケースを追加する
   - `src/server/routes/admin.php` の song-tags グループに `GET /{songTagId}` と `PUT /{songTagId}` を追加する（`/search` より後に配置）
   - `src/server/app/Providers/Domain/SongServiceProvider.php` の `registerSongTag()` に Get/Update のバインディングを追加する
   - 依存: ステップ 1, 4

6. **サーバー: GetInteractor・UpdateInteractor のユニットテストを追加する**
   - `src/server/tests/Unit/Song/Application/Interactors/Tag/GetInteractorTest.php` を追加する
   - `src/server/tests/Unit/Song/Application/Interactors/Tag/UpdateInteractorTest.php` を追加する
   - 依存: ステップ 4

7. **admin BFF: `song-tags.ts` に GET / PUT エンドポイントを追加する**
   - `src/admin/src/server/routes/song-tags.ts` に `GET /:songTagId` と `PUT /:songTagId` を追加する
   - 依存: ステップ 1（生成クライアントに `songTagServiceGetSongTag` が存在すること）

8. **admin UI: `EditableForm.tsx` を追加し `SearchList.tsx` を修正する**
   - `src/admin/src/components/song-tag/EditableForm.tsx` を新規作成する（`performer/EditableForm.tsx` と同構造。削除ボタンは含めない。更新後は `/song-tags${back}` へリダイレクト）
   - `src/admin/src/components/song-tag/SearchList.tsx` の各行に `onClick` を追加して `/song-tags/${tag.songTagId}?back=...` へ遷移させる
   - 依存: ステップ 7

9. **admin UI: `src/admin/src/pages/song-tags/[id].astro` を追加する**
   - `performer/[id].astro` と同構造。401 時は `/auth/login` へリダイレクト
   - 依存: ステップ 8

## Decision Log

- 2026-04-29: Performer の Get/Update パターンをそのまま SongTag に適用する。業務ルール（名前ユニーク制約・orderNo 更新）が同じで差分は Permission 定数と型名のみ。
- 2026-04-29: `SongTagRepositoryInterface` に `find` を追加する。Get/Update 両 Interactor が単体取得を必要とし、Performer と同じ Interactor→Repository パターンに合わせる。
- 2026-04-29: EditableForm から削除ボタンを除外する。削除機能は Non-Scope。
- 2026-04-29: `admin.php` のルート順は `search` → `{songTagId}` の順を維持する。Laravel は定義順に評価するため静的セグメントをパラメータセグメントより先に登録する必要がある。

## Validation

- **AC1**: `mise run contract:compile:admin` 成功後、生成 OAS に `getSongTag` と `SongTagGetResponse` が存在すること
- **AC2**: `GetInteractorTest` が通ること。手動で `GET /admin/v1/song-tags/{存在するID}` が 200、存在しない ID が 404 を返すこと
- **AC3**: `UpdateInteractorTest` が通ること。手動で `PUT /admin/v1/song-tags/{ID}` が成功 200・バリデーションエラー 422 を返すこと
- **AC4**: ブラウザで `/song-tags/{id}` にアクセスし、EditableForm にタグ名と表示順が表示されること
- **AC5**: 検索一覧でタグ行をクリックすると `/song-tags/{id}?back=...` へ遷移すること
- **AC6**: 更新成功後に「更新しました」フラッシュ表示で `/song-tags` 一覧（元クエリ付き）に戻ること
- **静的検査**: `mise run ecs`, `mise run phpstan`, `mise run arkitect` がエラーなく通ること
- **admin ビルド**: `cd src && bun --filter admin build` が成功すること
