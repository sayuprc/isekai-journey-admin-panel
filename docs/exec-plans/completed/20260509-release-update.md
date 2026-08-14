# Title

Release 更新

## Status

completed

## Background

`Release` は `docs/exec-plans/completed/20260509-release-list.md`、`20260509-release-next-feature.md`、`20260509-release-detail.md` により、一覧・作成・詳細まで completed になった。一方で `src/contracts/src/admin/releases/service.tsp` には update API がなく、`src/admin/src/pages/releases/[id].astro` と `src/admin/src/components/release/DetailView.tsx` も read-only のままで、保存済み `Release` の基本情報を管理画面から修正できない

`docs/product-specs/20260503-song-media-admin-phase1/models.md` では Phase 1 の admin に `Release` CRUD と、一覧 / 作成 / 編集 / 詳細の基本導線が必要とされている。さらに `Release` の作成 / 編集フローでは、基本情報入力に加えて「収録楽曲を検索して追加」「曲順を調整」して保存することが前提になっている。既存の `Person` / `Media` / `Song` は `[id].astro` で更新フォームを提供しており、`Release` だけ詳細確認で止まっている状態は管理機能として不足している

## Goal

管理画面で保存済み `Release` の基本情報と収録楽曲をまとめて更新できる最小機能として、`Release` 更新 API、admin BFF、`[id]` 画面の編集導線を追加する。これにより `Release` の必須 CRUD のうち更新を満たし、`Release` 編集フローを 1 API + 1 画面の粒度で閉じる

## Scope

- `src/contracts/src/admin/releases/service.tsp` と `transport.tsp` に `Release` 更新 API と request / response を追加する
- `src/server/packages/Release/Application/Admin/UseCase` と `src/server/packages/Release/Infrastructures/ReleaseRepository.php` に、`Release` 基本情報と `trackEntries` を更新する use case と永続化処理を追加する
- `src/server/app/Http/Controllers/Api/Admin/V1/Release`、presenter / converter、`src/server/packages/Release/Route/ReleaseRouteMap.php`、`src/server/routes/admin.php` に更新 API の HTTP 入口を追加する
- `src/admin/src/server/routes/releases.ts` に `PUT /releases/:releaseId` 相当の BFF を追加する
- `src/admin/src/server/routes/songs.ts` の既存検索 BFF を利用し、`src/admin/src/pages/releases/[id].astro` と `src/admin/src/components/release` 配下を更新して、`Release` 詳細画面から収録楽曲の検索追加・削除・曲順編集まで行えるようにする
- 今回の更新対象は `title`、`type`、`distributionType`、`releasedOn`、`description`、`isDisplay` と `trackEntries` に閉じる

## Non-Scope

- `Release` 削除 API / UI
- `Song` 詳細への所属 `Release` 表示
- viewer 側の `Release` 表示改善
- `GET /releases/{releaseId}` の response 形状変更
- `Song` の新規作成 / 更新フロー自体への変更

## Acceptance Criteria

- admin contracts に `Release` 更新 API が定義され、基本情報 6 項目に加えて `trackEntries` を受け取って既存 `Release` を更新できる
- server 側に `Release` 更新処理が追加され、存在しない `releaseId` は 404、権限不足は 403、入力不正は 422 で扱われる
- admin BFF から `Release` 更新 API を呼び出せる
- 管理画面の `src/admin/src/pages/releases/[id].astro` で現在値を編集し、既存 `Song` 検索を使って収録楽曲を追加・削除・並び替えして保存できる
- `GET /releases/{releaseId}` が返す `songs: ReleaseReferencedSong[]` を初期表示に使い、既存 `Release` の収録順が編集 UI に復元される
- 保存成功後に `/releases` へ戻り、再表示時も保存した `trackEntries` の内容と順序が detail API で確認できる

## Steps

1. ✅ `src/contracts/src/admin/releases/transport.tsp` と `service.tsp` に `@put updateRelease(@path releaseId: uuid, @body request: ReleaseUpdateRequest)` を追加する。`ReleaseUpdateRequest` は create の 6 項目に `trackEntries: TrackEntry[]` を加え、`ReleaseUpdateResponse` は既存 `Release` model を返す最小形にする
2. ✅ contract 変更に追随して `src/contracts/generated/oas/IsekaiObservatory.Admin.v1.yaml`、`src/server/Generated/lib/Api/ReleaseApi.php`、`src/server/Generated/lib/Model/ReleaseUpdateRequest.php` / `ReleaseUpdateResponse.php`、`src/admin/src/generated/*` を再生成する。`GET /releases/{releaseId}` の response は変えず、update request だけで収録楽曲編集を受け取れることを確認する
3. ✅ `src/server/packages/Release/Domain/Services/ReleaseIntegrityService.php` と `src/server/packages/Release/Application/Admin/UseCase/Update` を更新し、`trackEntries` を含む `Release` 再構築を update 用に扱えるようにする。`UpdateInputData` / `UpdateOutputData` / `UpdateUseCase` では `Permission::WriteRelease`、transaction、`ReleaseRepositoryInterface::find()`、audit log、未存在 404、入力不正 422 の変換を既存 update パターンに揃える
4. ✅ `src/server/packages/Release/Domain/Models/ReleaseRepositoryInterface.php` と `src/server/packages/Release/Infrastructures/ReleaseRepository.php` を更新し、request で受けた `trackEntries` を release-song 関連へ正しく永続化できるようにする。追加・削除・曲順変更が 1 回の save で反映されることを優先し、既存の upsert / relation 保存ロジックを流用できるかを先に確認する
5. ✅ `src/server/app/Http/Controllers/Api/Admin/V1/Release/UpdateReleaseController.php`、`UpdatePresenter.php`、必要なら `Converter.php`、`src/server/packages/Release/Route/ReleaseRouteMap.php`、`src/server/routes/admin.php` を更新し、`PUT /admin/v1/releases/{releaseId}` を配線する。controller では request の `trackEntries` を use case input へ正規化し、presenter は既存 `Release` response へ揃える
6. ✅ `src/admin/src/server/routes/releases.ts` に `PUT /releases/:releaseId` を追加し、生成 client の `releaseServiceUpdateRelease` を `withAuthRetry` / `resolveApiResponse` パターンで呼ぶ。body schema は基本情報 6 項目に `trackEntries: Array<{ songId: string; trackNo: number }>` を含める
7. ✅ `src/admin/src/components/release/DetailView.tsx` を編集フォームへ作り替え、`GET /releases/{releaseId}` の `songs: ReleaseReferencedSong[]` から初期 `trackEntries` UI を復元する。追加済み一覧、削除、上下移動による曲順編集を持つ state を導入し、`src/admin/src/components/song/MediaSection.tsx` の検索追加・並び替えパターンを必要最小限で流用する
8. ✅ `src/admin/src/pages/releases/[id].astro` と必要な release component を更新し、`src/admin/src/server/routes/songs.ts` の既存 `GET /songs/search` BFF を使った楽曲検索導線を同画面に追加する。検索結果から未追加の `Song` を選んで `trackEntries` に積み、保存成功後は `/releases` へ戻し、404 / 422 の初期取得失敗は既存パターンに揃える
9. ✅ server の integration / feature test と admin lint を追加・実行する。少なくとも `src/server/tests/Integration/Release/Application/Admin/UseCase/Update`、`src/server/tests/Feature/Api/Admin/V1/Release/UpdateReleaseTest.php` を足し、`trackEntries` の追加・削除・並び替えが保存されること、404 / 403 / 422、admin 側で検索追加 UI を含む lint が通ることを確認する

## Decision Log

- 2026-05-09: `trackEntries` は update request で明示的に受け取る。`Release` 編集の責務に収録楽曲の追加・削除・曲順変更を含める要件になったため、server 側の暗黙引き継ぎではなく request を Source of Truth にする
- 2026-05-09: `Song` 検索は `src/admin/src/server/routes/songs.ts` の既存 `GET /songs/search` BFF を再利用し、Release 専用の検索 API は増やさない。`1 API + 1 画面` の粒度を維持しつつ、既存 `Song` 検索条件と認可処理をそのまま使えるため
- 2026-05-09: `GET /releases/{releaseId}` の response は変えず、既存の `songs: ReleaseReferencedSong[]` から初期表示用の `trackEntries` state を復元する。詳細 read API を安定させたまま update request のみを拡張した方が変更範囲が小さいため
- 2026-05-09: update 成功後は同画面に留めず `/releases` 一覧へ戻す。既存 `Media` / `Song` update の操作感に合わせつつ、保存結果の再取得やクエリ復元を複雑化させず最小差分で完結させるため
- 2026-05-09: `ReleaseRepository::save()` の既存 delete → insert ロジックで `trackEntries` の追加・削除・並び替えをそのまま反映できたため、repository 層は新規 API を増やさず現行実装を維持した

## Validation

- `mise run contract:format:check`
- `docker compose exec php php artisan test tests/Integration/Release/Application/Admin/UseCase/Update tests/Feature/Api/Admin/V1/Release/UpdateReleaseTest.php`
- `mise run api:phpstan packages/Release app/Http/Controllers/Api/Admin/V1/Release app/Http/Presenters/Api/Admin/V1/Release tests/Integration/Release/Application/Admin/UseCase/Update tests/Feature/Api/Admin/V1/Release`
- `mise run admin:lint src/pages/releases/[id].astro src/components/release src/server/routes/releases.ts`
- 管理画面で `Release` 詳細を開き、既存収録楽曲が初期表示されることを確認する
- 管理画面で `Song` 検索から収録楽曲を追加し、保存後の詳細再取得で追加分が表示されることを確認する
- 管理画面で収録楽曲を削除し、保存後の詳細再取得で削除分が消えていることを確認する
- 管理画面で収録楽曲の並び順を変更し、保存後の詳細再取得で `trackNo` 順が反映されていることを確認する
- `git diff --name-only` で `Release` 削除、`Song` 詳細への所属 `Release` 表示、`GET /releases/{releaseId}` response 変更が差分に入っていないことを確認する
