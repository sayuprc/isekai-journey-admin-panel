# Title

Release 一覧

## Status

completed

## Background

`docs/exec-plans/completed/20260508-release-foundation.md` と `20260508-release-next-step.md` により、`Release` の基盤、repository interface、contracts / BFF の器までは揃っている。一方で admin 側は `src/admin/src/server/routes/releases.ts` が空の stub のままで、`src/admin/src/pages/releases` や一覧コンポーネントはまだ存在せず、最初の user-facing な縦切りが未着手である

既存の `songs` / `persons` / `media` はいずれも「search API + BFF `/search` + index 画面」を最初の最小単位として持っており、この形が admin の実装パターンとして安定している。`Release` でも同じ入口から着手すれば、基盤の妥当性確認、契約の具体化、画面導線の最初の接続を最小変更で行える

また、`Release` の作成や詳細は `TrackEntry` 編集や関連楽曲取得を伴いやすく、一覧より依存範囲が広い。共通基盤の直後に着手する 1 API + BFF + 画面の単位としては、検索条件と表示項目を `Release` 自身の属性に閉じられる一覧が最も自然である

## Goal

管理画面で `Release` を検索・閲覧できる最小機能として、`Release` 一覧 API、admin BFF、一覧画面を追加する。これにより、後続の作成・詳細・更新が依存できる契約と UI 導線の基準を確立する

## Scope

- `src/contracts/src/admin/releases` に、一覧取得のための `service.tsp` / `transport.tsp` と必要な request / response 型を追加する
- `src/server/packages/Release` に、admin 一覧取得用の query / use case / repository 実装と、それを呼び出す HTTP 入口を追加する
- `src/admin/src/server/routes/releases.ts` に `GET /releases/search` の BFF を実装する
- `src/admin/src/pages/releases/index.astro` と、既存の `songs` / `persons` / `media` の一覧に対応する `src/admin/src/components/release/SearchList.tsx` 相当を追加する
- 一覧画面で扱う項目は、少なくとも `title`、`type`、`distributionType`、`releasedOn`、`isDisplay` とページネーションに閉じる

## Non-Scope

- `src/admin/src/pages/releases/[id].astro` や詳細画面の追加
- `src/admin/src/pages/releases/create` や作成フォームの追加
- `TrackEntry` の編集 UI、楽曲検索モーダル、収録曲の保存処理
- `Release` の更新・削除 API
- viewer 側の `Release` モック置換
- `Song` 詳細から所属 `Release` を表示する導線の追加
- `releases` / `release_track_entries` の schema や domain model の再設計

## Acceptance Criteria

- admin contracts に `Release` 一覧取得 API が定義され、生成対象の request / response が `Release` 一覧用途として解釈できる
- server 側に `Release` 一覧取得の実装が追加され、`title`、`type`、`distributionType`、`isDisplay` など一覧で必要な条件で検索できる
- admin BFF の `GET /releases/search` が server API を呼び出し、一覧画面へそのまま渡せる形式で応答する
- `src/admin/src/pages/releases/index.astro` から到達できる一覧画面が追加され、既存一覧画面と同様に検索、空状態、エラー状態、ページネーションを持つ
- 今回の変更だけでは `Release` の作成、詳細、収録曲編集はまだ提供されない

## Steps

1. ✅ `src/contracts/src/admin/releases/main.tsp` を `media` / `persons` と同じ 3 ファイル構成へ拡張し、`src/contracts/src/admin/releases/service.tsp` と `transport.tsp` を追加する。`GET /releases/search` の query を `title` / `type` / `distributionType` / `is_display` / `page` / `per_page` に絞って定義し、一覧レスポンスは `Release[]` と `maxPage` を返す形にする
2. ✅ `src/contracts/src/admin/admin-users/domain.tsp` も必要最小限で更新し、`PermissionValue` に `ReadRelease` を追加する。server 側の認可と admin 生成型をずらさないため、契約変更後に `mise run contract:compile:admin`、`mise run api:generate`、`mise run admin:generate` で OpenAPI と生成物を更新する
3. ✅ `src/server/packages/Release/Domain/Models/ReleaseRepositoryInterface.php` を一覧取得に使える interface へ拡張し、`src/server/packages/Release/Domain/Criteria/ReleaseSearchCriteria.php`、`src/server/packages/Release/Application/Admin/UseCase/Search/SearchInputData.php`、`SearchOutputData.php`、`SearchUseCase.php` を追加する。検索条件は contract と同じく `Release` 自身の属性とページネーションだけに閉じる
4. ✅ `src/server/packages/Release/Infrastructures/ReleaseRepository.php` を追加して `App\Models\Release\Release` から一覧検索と `maxPage` を実装し、`title` の部分一致、`type`、`distribution_type`、`is_display` を絞り込めるようにする。合わせて `src/server/packages/AdminUser/Domain/Models/Permission.php` に `ReadRelease` を追加し、`SearchUseCase` は既存一覧 API と同じく権限チェック経由で実行する
5. ✅ `src/server/app/Http/Controllers/Api/Admin/V1/Release/SearchReleaseController.php`、`src/server/app/Http/Presenters/Api/Admin/V1/Release/Converter.php`、`SearchPresenter.php`、`src/server/packages/Release/Route/ReleaseRouteMap.php`、`src/server/app/Providers/Domain/ReleaseServiceProvider.php` を追加し、`src/server/routes/admin.php` と `src/server/bootstrap/providers.php` に `GET /admin/v1/releases/search` の配線を登録する
6. ✅ `src/admin/src/server/routes/releases.ts` の stub を `media.ts` 相当の BFF に置き換え、生成された `releaseServiceSearchReleases` を呼ぶ `GET /releases/search` を実装する。query schema は UI から送る `title` / `type` / `distribution_type` / `is_display` / `page` / `per_page` に限定し、server API のレスポンスをそのまま返す
7. ✅ `src/admin/src/pages/releases/index.astro` と `src/admin/src/components/release/SearchList.tsx` を追加し、`media` 一覧に近い検索フォーム、一覧テーブル、空状態、エラー状態、ページネーションを実装する。表示列は `title`、`type`、`distributionType`、`releasedOn`、`isDisplay` に絞り、作成・詳細・収録曲編集への導線は付けない。到達導線として `src/admin/src/components/Sidebar.tsx` に `Release` 一覧リンクも追加する
8. ✅ server と admin の最小検証を追加・実行する。少なくとも `src/server/tests/Integration/Release/Application/Admin/UseCase/Search/SearchUseCaseTest.php` を追加して検索条件と `maxPage` を確認し、仕上げに `mise run contract:format:check`、`mise run api:test src/server/tests/Integration/Release/Application/Admin/UseCase/Search/SearchUseCaseTest.php`、`mise run api:phpstan src/server/packages/Release src/server/app/Http/Controllers/Api/Admin/V1/Release src/server/app/Http/Presenters/Api/Admin/V1/Release`、`mise run admin:lint src/admin/src/components/release/SearchList.tsx src/admin/src/server/routes/releases.ts src/admin/src/pages/releases/index.astro` を通す

## Decision Log

- 2026-05-09: `Release` の最初の縦切りは `search API + BFF + index 画面` に限定する。`TrackEntry` や関連楽曲を巻き込む作成・詳細より依存範囲が狭く、基盤の妥当性を最短で確認できるため
- 2026-05-09: 一覧の検索条件と表示項目は `Release` 自身の属性に閉じ、収録曲数は今回の API 契約から外す。`track_entries` 集計を入れると query と表示責務が広がり、最小の 1 単位から外れるため
- 2026-05-09: 認可は後回しにせず `ReadRelease` を追加する。既存の admin 一覧 API が use case ごとに権限を持つため、ここだけ既存権限の流用や無認可で実装すると責務境界が崩れるため
- 2026-05-09: contract 変更後すぐに OpenAPI と generated code を更新してから実装へ進む。BFF と presenter の型を手書きで先行させるより、Source of Truth を固定した方が差分が単純になるため
- 2026-05-09: admin 画面の導線は新規作成ボタンや詳細リンクを出さず、一覧閲覧専用に留める。Issue の Non-Scope を UI 上でも明示し、未実装導線からの期待値ずれを避けるため

## Validation

- `src/contracts/src/admin/releases/service.tsp` と `transport.tsp` が追加され、生成された `src/server/Generated/` と `src/admin/src/generated/` に `Release` 一覧 API が反映されていることを確認する
- `GET /admin/v1/releases/search` が `title` / `type` / `distributionType` / `isDisplay` / ページネーションで検索でき、`Release[]` と `maxPage` を返すことを `src/server/tests/Integration/Release/Application/Admin/UseCase/Search/SearchUseCaseTest.php` で確認する
- `src/admin/src/server/routes/releases.ts` の `GET /releases/search` が server API を呼び、401 時の再認証、正常時のレスポンス透過、異常時のエラー解決が既存 BFF と同じ振る舞いであることを確認する
- `src/admin/src/pages/releases/index.astro` と `src/admin/src/components/release/SearchList.tsx` で、検索、空状態、エラー状態、ページネーション、URL クエリ同期が動作し、作成・詳細・収録曲編集の導線が追加されていないことを確認する
- `mise run contract:format:check`、`mise run api:phpstan ...`、対象 Integration Test、`mise run admin:lint ...` を通し、今回の差分が `Release` 一覧の contract / server / BFF / UI に閉じていることを確認する
