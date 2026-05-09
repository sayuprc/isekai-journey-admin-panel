# Title

Release 詳細

## Status

in-progress

## Background

`Release 一覧` は [20260509-release-list.md](/tmp/feature-release/docs/exec-plans/completed/20260509-release-list.md) で完了し、`src/admin/src/pages/releases/index.astro` から `Release` を検索・閲覧する入口はできた。一方で、一覧の次に自然につながる `Release` 単体の確認画面はまだなく、保存済みの基本情報や `trackEntries` をまとめて確認できない。

`Release 作成` や更新を先に進めると、`TrackEntry` の楽曲検索・追加・曲順編集・保存まで同時に扱う必要があり、1 API + BFF + 画面の単位としては重い。既存の server には `Release` 集約と `TrackEntry` モデル、admin には `media` / `songs` / `persons` の detail 実装パターンがあるため、まずは read 系として `Release 詳細` を通すのが最小である。

また、`docs/product-specs/20260503-song-media-admin-phase1/models.md` でも `Release` 詳細は「登録済みリリースの確認画面」と「保存済みの Release と収録楽曲をまとめて閲覧できること」が役割になっている。一覧の次の縦切りとして詳細を定義すれば、後続の create / update が依存する参照契約と UI 導線を安定させられる。

## Goal

管理画面で 1 件の `Release` を取得して、基本情報と `trackEntries` を確認できる詳細画面を追加する。これにより、一覧から個別の `Release` 内容を辿れる read 系の最小ユースケースを成立させる。

## Scope

- `src/contracts/src/admin/releases/service.tsp` と `src/contracts/src/admin/releases/transport.tsp` に `GET /releases/{releaseId}` の契約を追加し、`src/contracts/src/admin/releases/domain.tsp` の `Release` を詳細取得 API で利用する
- `src/contracts/src/admin/admin-users/domain.tsp` の `ReadRelease` を利用する前提で、OpenAPI と generated client/server を `Release` 詳細取得に追従させる
- `src/server/packages/Release/Domain/Models/ReleaseRepositoryInterface.php` と `src/server/packages/Release/Infrastructures/ReleaseRepository.php` に `releaseId` 指定の取得口を追加し、`src/server/packages/Release/Application/Admin/UseCase/Get/*` を新設して詳細取得 use case を実装する
- `src/server/app/Http/Controllers/Api/Admin/V1/Release/GetReleaseController.php`、`src/server/app/Http/Presenters/Api/Admin/V1/Release/GetPresenter.php`、既存 `Converter.php`、`src/server/packages/Release/Route/ReleaseRouteMap.php`、`src/server/routes/admin.php` を更新し、server API の `GET /admin/v1/releases/{releaseId}` を配線する
- `src/admin/src/server/routes/releases.ts` に `GET /releases/:releaseId` の BFF を追加し、generated client 経由で server API を透過する
- `src/admin/src/pages/releases/[id].astro` と `src/admin/src/components/release/Detail.tsx` 相当の表示コンポーネントを追加し、`title`、`type`、`distributionType`、`releasedOn`、`description`、`isDisplay`、`trackEntries` を表示する
- `trackEntries` は既存 `Release` 集約と contract が持つ `trackNo` / `songId` に閉じて表示し、必要なら `songId` を使った `src/admin/src/pages/songs/[id].astro` への導線だけを持たせる

## Non-Scope

- `src/admin/src/pages/releases/create` や作成フォームの追加
- `Release` の更新・削除 API と編集 UI
- `TrackEntry` の追加・削除・曲順編集・保存
- `trackEntries` 表示のために `Song` 名や追加メタデータを join して返すこと
- `src/admin/src/pages/releases/index.astro` への検索条件追加や一覧仕様の見直し
- `Song` 詳細へ所属 `Release` 一覧を表示する変更
- viewer 側の `Release` 表示や public API の追加

## Acceptance Criteria

- admin contracts に `GET /releases/{releaseId}` が追加され、`releaseId` を path に取って `Release` 1 件を返す generated 型が server / admin の両方で参照できる
- server 側で存在する `releaseId` を指定したとき、`title`、`type`、`distributionType`、`releasedOn`、`description`、`isDisplay`、`trackEntries` を含む `Release` 詳細を返し、存在しない `releaseId` では not found を返す
- admin BFF の `GET /releases/:releaseId` が server API の成功・401・not found を既存 detail BFF と同じ責務分担で扱える
- `src/admin/src/pages/releases/[id].astro` から表示される詳細画面で、基本情報 6 項目と `trackEntries` の一覧を確認できる
- `trackEntries` 表示は `trackNo` と `songId` に閉じ、今回の変更だけでは曲名解決、収録曲編集、作成・更新導線は追加されない

## Steps

1. ✅ `src/contracts/src/admin/releases/service.tsp` と `src/contracts/src/admin/releases/transport.tsp` に `GET /releases/{releaseId}` と詳細レスポンスを追加し、必要なら `src/contracts/src/admin/releases/main.tsp` の export を調整して `Release` 詳細契約を定義する。
2. ✅ `src/contracts/src/admin/admin-users/domain.tsp` の `ReadRelease` を使う前提で `mise run contract:compile:admin`、`mise run api:generate`、`mise run admin:generate` を実行し、`src/contracts/generated/oas/IsekaiObservatory.Admin.v1.yaml`、`src/server/Generated/lib/Api/ReleaseApi.php`、`src/server/Generated/lib/Model/*Release*`、`src/admin/src/generated/*` の生成物を詳細 API に追従させる。
3. ✅ `src/server/packages/Release/Domain/Models/ReleaseRepositoryInterface.php` に `findById` 相当の取得口を追加し、`src/server/packages/Release/Infrastructures/ReleaseRepository.php` で `trackEntries` を含む単体取得を実装する。一覧検索用の `search` は引き続き一覧用途に閉じ、詳細取得の責務を分離する。
4. ✅ `src/server/packages/Release/Application/Admin/UseCase/Get/GetInputData.php`、`GetOutputData.php`、`GetUseCase.php` を追加し、`ReadRelease` 権限で `releaseId` を受けて 1 件取得し、未存在時は not found を返す read 系 use case を実装する。
5. ✅ `src/server/app/Http/Controllers/Api/Admin/V1/Release/GetReleaseController.php`、`src/server/app/Http/Presenters/Api/Admin/V1/Release/GetPresenter.php` を追加し、既存 `src/server/app/Http/Presenters/Api/Admin/V1/Release/Converter.php` を詳細レスポンス生成に再利用できる形へ整える。あわせて `src/server/packages/Release/Route/ReleaseRouteMap.php` と `src/server/routes/admin.php` に `GET /admin/v1/releases/{releaseId}` を配線する。
6. ✅ `src/admin/src/server/routes/releases.ts` に `releaseServiceGetRelease` を使う `GET /:releaseId` を追加し、`src/admin/src/server/routes/media.ts` と同様に `withAuthRetry` + `resolveApiResponse` で server API の成功・401・404 をそのまま扱える BFF にする。
7. ✅ `src/admin/src/pages/releases/[id].astro` を追加して一覧から遷移できる詳細ページを作り、`src/admin/src/components/release/Detail.tsx` を追加して `title`、`type`、`distributionType`、`releasedOn`、`description`、`isDisplay`、`trackEntries` を表示する。`trackEntries` は `trackNo` と `songId` 表示に閉じ、必要なら `songId` から `songs/[id]` へのリンクだけを持たせる。
8. ✅ `src/admin/src/components/release/SearchList.tsx` を更新して詳細画面への導線を明示し、`src/server/tests/Feature/Api/Admin/V1/Release/GetReleaseTest.php` を追加して found / not found を検証する。必要なら BFF か UI の既存テスト基盤を確認したうえで最小の回帰確認を追加する。
9. ✅ `mise run contract:format:check`、`cd src/server && mise run api:phpstan packages/Release app/Http/Controllers/Api/Admin/V1/Release app/Http/Presenters/Api/Admin/V1/Release`、`cd src/admin && mise run admin:lint src/components/release/Detail.tsx src/components/release/SearchList.tsx src/pages/releases src/server/routes/releases.ts`、`cd src/server && mise run api:test tests/Feature/Api/Admin/V1/Release/GetReleaseTest.php` で受け入れ条件を閉じる。

## Decision Log

- 2026-05-09: `Release 詳細` は既存の `media` / `songs` detail と同じく `GET /{id}` の contract → server API → BFF → Astro page の縦切りで実装する。一覧完了後に最小でつながる read 系であり、create/update より依存が軽いため。
- 2026-05-09: `trackEntries` は `trackNo` と `songId` の表示に限定し、`Song` 名 join や編集導線は今回のスコープから外す。Issue の最小縦切りを守り、一覧の次に必要な確認画面に責務を絞るため。
- 2026-05-09: repository には一覧検索と別に単体取得口を追加する。詳細だけが `trackEntries` を必須とするため、一覧 API に不要な関連取得を混ぜず責務分離を維持する。

## Validation

- contract: `mise run contract:compile:admin` と `mise run contract:format:check` を通し、`GET /releases/{releaseId}` と詳細レスポンスが OpenAPI / generated 型へ反映されることを確認する。
- server API: `src/server/tests/Feature/Api/Admin/V1/Release/GetReleaseTest.php` で found / not found を検証し、成功時に `title`、`type`、`distributionType`、`releasedOn`、`description`、`isDisplay`、`trackEntries` が返ることを確認する。
- 静的検査: `mise run api:phpstan packages/Release app/Http/Controllers/Api/Admin/V1/Release app/Http/Presenters/Api/Admin/V1/Release` と `mise run admin:lint ...` を実行し、server / BFF / UI の型整合を確認する。
- BFF / UI: `src/admin/src/pages/releases/[id].astro` から `GET /releases/:releaseId` を叩いたとき、401 はログインへリダイレクトし、404 は既存 detail と同じエラー表示責務に従うことを手元確認する。画面では基本情報 6 項目と `trackEntries` が表示され、曲名解決や編集導線が増えていないことを確認する。
