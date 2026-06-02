# Execution Plan — Viewer トップページ集計 API

実装計画（どう進めるか）を書く。問題定義は同ディレクトリの `issue.md` を参照する。

## Title

Viewer トップページ集計 API

## Status

completed

## Steps

1. ✅ `src/contracts/src/viewer/site-stats/{main.tsp,service.tsp,transport.tsp}` を新設し、`src/contracts/src/viewer/main.tsp` から import する。`@route("/site-stats")` の `SiteStatsService.getSiteStats()` を追加し、`SiteStatsResponse` は初回フィールドとして `songCount: int32;` を直下に持たせる。`@doc` と `@example` も既存 `songs` / `media` の書き方に揃える。
2. ✅ Step 1 に依存して `mise run contract:format:check` と `mise run contract:compile:viewer` を実行し、`src/contracts/generated/oas/IsekaiObservatory.Viewer.v1.yaml` を更新する。続けて `mise run viewer:generate` を実行し、`src/viewer/src/generated/` に `siteStatsServiceGetSiteStats` と `SiteStatsResponse` 型を反映する。`src/server/Generated/` は現行タスクでは Admin OAS 由来の生成物なので手動編集せず、差分が出ないことを確認する。
3. ✅ `src/server/packages/SiteStats/Route/ViewerSiteStatsRouteMap.php` を新設し、`case Get = 'viewer.site-stats.get';` を定義する。`src/server/routes/viewer.php` に `GetSiteStatsController` と route map の import を追加し、`Route::get('/site-stats', [GetSiteStatsController::class, 'handle'])->name(ViewerSiteStatsRouteMap::Get);` を `v1` 配下に登録する。
4. ✅ Step 3 に依存して、`src/server/packages/Song/Application/Viewer/Query/SongQueryServiceInterface.php` に公開楽曲数を返す `countDisplayable(): int` を追加し、`src/server/packages/Song/Infrastructures/Viewer/SongQueryService.php` に `Song::query()->where('is_display', true)->count()` で実装する。既存 `list()` / `get()` の `is_display = true` 条件と同じ公開判定を使う。
5. ✅ Step 4 に依存して、`src/server/packages/SiteStats/Application/Viewer/UseCase/Get/{GetUseCase.php,GetOutputData.php}` を追加する。`GetUseCase` は `SongQueryServiceInterface::countDisplayable()` を呼び、`Result<GetOutputData, UseCaseError>` の `Ok` として `songCount` を返す。
6. ✅ Step 5 に依存して、`src/server/app/Http/Controllers/Api/Viewer/V1/SiteStats/GetSiteStatsController.php` と `src/server/app/Http/Presenters/Api/Viewer/V1/SiteStats/GetPresenter.php` を追加する。controller は入力なしで `GetUseCase` を実行し、presenter は成功時に `['songCount' => $outputData->songCount]` を 200 で返し、失敗時は既存 presenter と同じ `ResolvesUseCaseError` を使う。
7. ✅ `src/server/tests/Feature/Api/Viewer/V1/SiteStats/GetSiteStatsTest.php` を追加する。`EntityFactory` / `EntityStore` で公開楽曲 2 件と非公開楽曲 1 件を投入し、`route(ViewerSiteStatsRouteMap::Get)` のレスポンスが `['songCount' => 2]` になることを `assertExactJson` で検証する。
8. ✅ Step 2 に依存して、`src/viewer/src/features/site-stats/{api.ts,types.ts}` を追加する。`api.ts` は generated client の `siteStatsServiceGetSiteStats` を `apiClient` 付きで呼び、`data` がない場合は既存 repository と同様に例外を投げ、成功時は `SiteStatsResponse` を返す。
9. ✅ Step 8 に依存して、`src/viewer/src/pages/index.astro` のトップページ楽曲数表示を `SITE_DATA.songs.length` から `const siteStats = await siteStatsRepository.get();` と `{siteStats.songCount}` に置き換える。見た目やコメントアウト済みの未提供セクションは変更しない。

## Decision Log

- 2026-06-02: API 名は `siteStats` / `GET /site-stats` にする。`summary` は対象範囲が曖昧で、`homeSummary` はトップページ専用の印象が強い。`siteStats` はサイト全体の公開件数集計を表し、将来 `mediaCount` / `releaseCount` を同じレスポンスへ追加しても意味が破綻しにくい。
- 2026-06-02: `SiteStatsResponse` は `songCount` を直下に置く。既存の list/detail response は用途ごとに root field を持つが、今回の集計は `songCount` / `mediaCount` / `releaseCount` を同列に拡張する想定なので、余分な `stats` wrapper を挟まない方が viewer 側の利用と将来拡張が単純になる。
- 2026-06-02: 集計 API の use case と route map は `SiteStats` package に置く。初回実装の集計対象は楽曲だけだが、将来 media / release を同じ API で集約するため、`Song` package に orchestration を持たせない。Song 側には公開楽曲数を返す query だけを追加する。
- 2026-06-02: `src/server/Generated/` は Viewer API の PHP 生成物としては使われておらず、現行 `mise run api:generate` も Admin OAS を入力にしている。Viewer API のサーバー側検証は `ViewerOpenApiValidator` が `src/contracts/generated/oas/IsekaiObservatory.Viewer.v1.yaml` を直接読むため、このタスクでは `src/server/Generated/` を手動更新しない。

## Validation

- Contract: `mise run contract:format:check` と `mise run contract:compile:viewer` を実行し、`src/contracts/generated/oas/IsekaiObservatory.Viewer.v1.yaml` に `GET /site-stats` と `SiteStatsResponse.songCount` が出力されること、既存 `GET /songs` / `GET /media` の path と response schema が意図せず変わっていないことを確認する。
- Viewer generated client: `mise run viewer:generate` を実行し、`src/viewer/src/generated/sdk.gen.ts` に `siteStatsServiceGetSiteStats`、`src/viewer/src/generated/types.gen.ts` に `SiteStatsResponse` が生成されることを確認する。
- Server behavior: `mise run api:test src/server/tests/Feature/Api/Viewer/V1/SiteStats/GetSiteStatsTest.php` を実行し、`is_display = true` の楽曲だけが `songCount` に数えられることを確認する。
- Server static checks: `mise run api:phpstan packages/SiteStats packages/Song app/Http/Controllers/Api/Viewer/V1/SiteStats app/Http/Presenters/Api/Viewer/V1/SiteStats tests/Feature/Api/Viewer/V1/SiteStats` と `mise run api:ecs packages/SiteStats packages/Song app/Http/Controllers/Api/Viewer/V1/SiteStats app/Http/Presenters/Api/Viewer/V1/SiteStats tests/Feature/Api/Viewer/V1/SiteStats` を実行する。
- Viewer build: `cd src && bun --filter viewer lint:check` と `cd src && bun --filter viewer build` を実行し、`src/viewer/src/pages/index.astro` が generated client 経由の `songCount` でビルドできることを確認する。
- 差分確認: `rg "SITE_DATA\\.songs\\.length" src/viewer/src/pages/index.astro` で mock 件数参照が消えていること、`git diff -- src/server/Generated` で Viewer API 由来の不要な生成差分がないことを確認する。
