# Title

楽曲タグ削除機能

## Status

completed

## Background

`src/contracts/src/admin/song-tags/service.tsp` には `deleteSongTag` 契約が既にあり、`src/admin/src/generated` にも削除クライアントが生成されている。一方で、`src/server` には SongTag の delete 用 use case / controller / presenter / route の接続がなく、`src/admin` 側にも詳細画面から削除を実行する導線がない。楽曲タグは一覧・検索・詳細・編集まで揃っているため、削除だけが未接続の状態になっている。

## Goal

管理画面の楽曲タグ詳細ページからタグを削除できるようにし、API・BFF・UI を既存契約に沿って接続する。

## Scope

- `src/server/packages/Song/Application/UseCase/Tag/Delete/` と `src/server/packages/Song/Application/Interactors/Tag/DeleteInteractor.php` を追加する
- `src/server/app/Http/Controllers/Api/SongTag/DeleteSongTagController.php` と `src/server/app/Http/Presenters/Api/SongTag/DeletePresenter.php` を追加する
- `src/server/routes/admin.php` に `DELETE /admin/v1/song-tags/{songTagId}` を追加し、`src/server/app/Providers/Domain/SongServiceProvider.php` で delete use case を bind する
- `src/server/tests/Unit/Song/Application/Interactors/Tag/DeleteInteractorTest.php` と `src/server/tests/Feature/Api/SongTag/DeleteSongTagTest.php` を追加する
- `src/admin/src/server/routes/song-tags.ts` に `DELETE /:songTagId` を追加する
- `src/admin/src/components/song-tag/EditableForm.tsx` に削除ボタンと確認ダイアログを追加する
- `src/admin/src/pages/song-tags/[id].astro` を前提に、削除後の戻り先とフラッシュ表示を連携する

## Non-Scope

- `src/contracts/src/admin/song-tags/service.tsp` と生成済み OpenAPI / admin SDK の契約変更
- 論理削除、soft delete、DB スキーマ変更
- 楽曲タグ以外の削除機能の追加
- 一覧・検索・作成・更新ロジックの仕様変更

## Acceptance Criteria

- 認証済みで `DELETE /admin/v1/song-tags/{songTagId}` を送ると、204 が返り、存在するタグは削除される。存在しない ID でも既存 delete 契約どおり 204 になる
- `src/admin/src/pages/song-tags/[id].astro` の詳細画面から削除を実行でき、成功時は一覧へ戻って「削除しました」が表示される
- `src/server/tests/Unit/Song/Application/Interactors/Tag/DeleteInteractorTest.php` と `src/server/tests/Feature/Api/SongTag/DeleteSongTagTest.php` が追加され、削除の成功系と主要な失敗系が検証される
- 今回の変更に契約ファイルの修正は含まれない

## Steps

1. ✅ `src/server/packages/Song/Domain/Models/Tag/SongTagRepositoryInterface.php` に `delete(SongTagId $songTagId): void` を追加し、`src/server/packages/Song/Infrastructures/Tag/SongTagRepository.php` に実装を足す。
2. ✅ `src/server/packages/Song/Application/UseCase/Tag/Delete/` に `DeleteInputData.php` と `DeleteUseCaseInterface.php` を追加し、`src/server/packages/Song/Application/Interactors/Tag/DeleteInteractor.php` を新規作成する。`Permission::WriteSong` を確認し、既存 delete 契約に合わせてそのまま `delete()` を呼ぶ流れにする。
3. ✅ `src/server/app/Http/Controllers/Api/SongTag/DeleteSongTagController.php` と `src/server/app/Http/Presenters/Api/SongTag/DeletePresenter.php` を追加し、`DELETE /admin/v1/song-tags/{songTagId}` の 204 / 422 / 401 / 403 の返却を既存 Presenter パターンに揃える。
4. ✅ `src/server/routes/admin.php` に `DELETE /song-tags/{songTagId}` を追加し、`src/server/app/Providers/Domain/SongServiceProvider.php` で `Song\Application\UseCase\Tag\Delete\DeleteUseCaseInterface` を `Song\Application\Interactors\Tag\DeleteInteractor` に bind する。
5. ✅ `src/server/tests/Unit/Song/Application/Interactors/Tag/DeleteInteractorTest.php` を追加して、成功時に repository の `delete()` が 1 回呼ばれることを確認する。
6. ✅ `src/server/tests/Feature/Api/SongTag/DeleteSongTagTest.php` を追加して、認証済み削除の 204、存在しない ID でも 204、未認証の 401 を `SongTagRouteMap::Delete` 経由で検証する。
7. ✅ `src/admin/src/server/routes/song-tags.ts` に `DELETE /:songTagId` を追加し、生成済みの `songTagServiceDeleteSongTag` を `resolveApiResponse` 経由で返す。
8. ✅ `src/admin/src/components/song-tag/EditableForm.tsx` に削除ボタンと確認ダイアログを追加し、削除成功時は `setFlash('削除しました')` の後に `back` 由来の一覧 URL へ戻す。既存の更新処理と同じ `listUrl` 解決を再利用する。
9. ✅ `src/admin/src/pages/song-tags/index.astro` に既に `FlashMessage` があるため、削除後のフラッシュ表示に追加変更は不要と確認する。

## Decision Log

- 2026-04-30: 削除は未存在 ID でも 204 にする。`src/contracts` の delete 契約と既存の Song / Performer / Creator の削除 API が 404 を持たないため、それに合わせる。
- 2026-04-30: 詳細画面の戻り先は `back` クエリから復元し、削除後も検索条件付き一覧へ戻す。新しい状態管理は増やさず、既存の詳細遷移パターンをそのまま使うため。
- 2026-04-30: 契約ファイルと生成物は変更しない。`songTagServiceDeleteSongTag` は既に生成済みなので、今回はサーバ接続と UI の導線だけを追加する。
- 2026-04-30: フラッシュ表示は一覧ページ側の `FlashMessage` をそのまま使う。詳細ページ `[id].astro` への追加変更は不要。

## Validation

- AC1: `src/server/tests/Feature/Api/SongTag/DeleteSongTagTest.php` で認証済み 204 と存在しない ID でも 204 になることを確認する。
- AC2: `src/admin/src/components/song-tag/EditableForm.tsx` から削除成功時に一覧へ戻ることと、一覧ページの `FlashMessage` で `setFlash('削除しました')` が表示されることを手動確認する。
- AC3: `src/server/tests/Unit/Song/Application/Interactors/Tag/DeleteInteractorTest.php` と `src/server/tests/Feature/Api/SongTag/DeleteSongTagTest.php` を実行して、成功系と主要な失敗系が通ることを確認する。
- AC4: `src/contracts/src/admin/song-tags/service.tsp` と `src/admin/src/generated` に差分が出ていないことを確認し、契約変更が混入していないことを保証する。
