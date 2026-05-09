# Title

Release 詳細

## Status

in-progress

## Background

`docs/exec-plans/completed/20260509-release-list.md` と `docs/exec-plans/completed/20260509-release-next-feature.md` により、`Release` は `search API + index 画面` と `create API + create 画面` まで実装済みになった。一方で `src/admin/src/pages/releases` には `create/index.astro` と `index.astro` しかなく、保存済み `Release` を確認するための `[id]` 詳細画面が存在しない。`src/admin/src/server/routes/releases.ts` と `src/server/routes/admin.php` にも詳細取得ルートはまだなく、一覧から次に遷移する read 導線が欠けている。

`docs/product-specs/20260503-song-media-admin-phase1/models.md` では、Phase 1 の `Release` は「一覧 / 作成 / 詳細」の責務で整理されており、詳細画面では `Release` の基本情報に加えて収録楽曲一覧を確認できることが求められている。現状は作成直後に一覧へ戻るだけで、保存した内容や将来の収録曲編集結果を確認する受け皿がない。

また、`Person` と `Media` はどちらも `GET /{id}` と `[id].astro` を持っており、admin の既存パターンとして「一覧 / 作成 / 詳細」が揃っている。`Release` でも次は同じパターンで詳細取得 API と詳細画面を追加するのが自然であり、その次の更新や収録曲編集の土台にもなる。加えて、現行の `Release` contract / domain の `trackEntries` は `songId` と `trackNo` のみで、詳細画面で求められる「収録楽曲一覧」の表示には楽曲タイトルや遷移先を補う read model が別途必要になる。

## Goal

管理画面で保存済み `Release` を確認できる最小機能として、`Release` 詳細取得 API、admin BFF、詳細画面を追加する。これにより作成済みデータの確認導線を整え、後続の更新・収録曲編集が依存できる read 導線を成立させる。

## Scope

- `src/contracts/src/admin/releases/service.tsp` と `transport.tsp` に `GET /releases/{releaseId}` を追加し、詳細画面で必要な response 形状を定義する
- 必要に応じて `src/contracts/src/admin/releases/domain.tsp` に、収録楽曲一覧の表示専用 model を追加する
- `src/server/packages/Release/Application/Admin/UseCase/Get`、`src/server/packages/Release/Application/Admin/Query`、`src/server/packages/Release/Infrastructures` に、`Release` 本体取得と収録楽曲の表示用 read model を返す詳細取得処理を追加する
- `src/server/app/Http/Controllers/Api/Admin/V1/Release`、presenter / converter、`src/server/packages/Release/Route/ReleaseRouteMap.php`、`src/server/routes/admin.php` に詳細取得の HTTP 入口を追加する
- `src/admin/src/server/routes/releases.ts` に `GET /releases/:releaseId` の BFF を追加する
- `src/admin/src/pages/releases/[id].astro` と `src/admin/src/components/release/DetailView.tsx` を追加し、一覧から遷移できる詳細表示画面を実装する
- `src/admin/src/components/release/SearchList.tsx` に詳細画面への導線を追加する
- 今回の表示対象は `title`、`type`、`distributionType`、`releasedOn`、`description`、`isDisplay` と、曲名・曲順を確認できる収録楽曲一覧に閉じる

## Non-Scope

- `Release` 更新・削除 API
- 収録楽曲の検索、追加、曲順編集 UI
- `Song` 詳細への所属 `Release` 表示
- `src/admin/src/components/release/CreateForm.tsx` の保存後遷移変更
- 一覧・作成画面の大幅なリデザイン
- viewer 側の `Release` 表示改善

## Acceptance Criteria

- admin contracts に `Release` 詳細取得 API が定義され、`releaseId` を指定すると `Release` の基本情報と、画面表示に必要な収録楽曲一覧を取得できる
- server 側に `Release` 詳細取得処理が追加され、不正な `releaseId` は HTTP レイヤーで 404、存在しない `releaseId` は 404 になり、正常系では曲順順の収録楽曲一覧を返せる
- admin BFF の `GET /releases/:releaseId` が server API を呼び出し、既存詳細 BFF と同じ認証リトライとエラー変換で応答する
- `src/admin/src/pages/releases/[id].astro` で、タイトル、種別、流通形態、発売日、説明、表示有無、収録楽曲一覧を確認できる
- 一覧画面から各 `Release` の詳細画面へ遷移でき、収録楽曲が未登録の `Release` では空状態を表示する
- 今回の画面には編集・削除・収録曲追加の操作は出さず、read-only の詳細確認に留める

## Steps

1. ✅ `src/contracts/src/admin/releases/domain.tsp` と `transport.tsp` を更新し、詳細画面用の収録楽曲表示 model と `ReleaseGetResponse` を追加する。`trackEntries` の生データとは別に、`songId`、`title`、`trackNo` を持つ表示専用 model を定義し、`service.tsp` に `GET /releases/{releaseId}` を追加する。
2. ✅ contract 変更に合わせて `mise run contract:compile:admin`、`mise run api:generate`、`mise run admin:generate` で生成物を更新し、server / admin の generated code に `Release` 詳細取得 API が反映される状態を先に作る。
3. ✅ `src/server/packages/Release/Application/Admin/UseCase/Get` を追加し、`GetInputData`、`GetOutputData`、`GetUseCase` を実装する。`Person` / `Song` の get use case パターンに合わせ、`releaseId` のバリデーション、`ReadRelease` 権限、404 返却の責務をここに閉じる。
4. ✅ `src/server/packages/Release/Application/Admin/Query` と `src/server/packages/Release/Infrastructures` に、`Release` 本体と収録楽曲の表示用 read model を組み立てる処理を追加する。`TrackEntry` だけでは曲名を出せないため、`songs` を参照して `songId`、`title`、`trackNo` を曲順順で返す read model を別に用意する。
5. ✅ `src/server/app/Http/Controllers/Api/Admin/V1/Release/GetReleaseController.php`、`src/server/app/Http/Presenters/Api/Admin/V1/Release/GetPresenter.php`、必要な converter 拡張、`src/server/packages/Release/Route/ReleaseRouteMap.php`、`src/server/routes/admin.php` を更新し、`GET /admin/v1/releases/{releaseId}` を配線する。
6. ✅ `src/admin/src/server/routes/releases.ts` に `GET /:releaseId` を追加し、生成済み client の `releaseServiceGetRelease` 相当を呼び出せるようにする。既存詳細 BFF と同じ `withAuthRetry` と `resolveApiResponse` の形に揃える。
7. ✅ `src/admin/src/pages/releases/[id].astro` と `src/admin/src/components/release/DetailView.tsx` を追加し、基本情報と収録楽曲一覧を表示する read-only 画面を実装する。`status !== 200` のときは既存詳細画面と同様にエラー表示へ流し、空の収録楽曲には空状態を出す。
8. ✅ `src/admin/src/components/release/SearchList.tsx` に詳細画面への導線を追加する。タイトル列や行導線から `/releases/{id}` へ遷移できるようにし、新規作成導線との責務衝突を避ける。
9. ✅ server と admin の最小検証を追加・実行する。少なくとも `Release` 詳細取得の integration / feature test、`mise run contract:format:check`、`mise run api:phpstan ...`、`mise run admin:lint ...` を通し、invalid id / 404 / 空の収録楽曲 / 正常系の表示を確認する。

## Decision Log

- 2026-05-09: `Release` の次の 1 単位は update ではなく get/detail にする。create 後の確認導線がないまま更新へ進むと、保存結果を人が確認できず既存 admin の画面構成とも揃わないため。
- 2026-05-09: 詳細 API は domain の `trackEntries` をそのまま返すだけでなく、曲名付きの表示専用 read model を追加する。仕様上の「収録楽曲一覧」を成立させるには `songId` と `trackNo` だけでは不足するため。
- 2026-05-09: 今回の詳細画面は read-only に留め、編集や収録曲追加導線は出さない。`1 API + 1 画面` の粒度を維持しつつ、次の update / track entry 編集タスクと責務を分離するため。
- 2026-05-09: 一覧から詳細への導線は今回追加するが、作成画面の保存後遷移は変更しない。既存の create 完了 UX を広げず、詳細画面の責務を「確認」に限定するため。
- 2026-05-09: `releaseId` の不正形式は `GetUseCase` 単体では 422 に変換できるが、Feature 経由では OpenAPI validator の経路で 404 になる。HTTP レイヤーの実挙動に合わせ、Feature test では 404 を期待し、use case の 422 は Integration Test で担保する。

## Validation

- `src/contracts/src/admin/releases/service.tsp` と `transport.tsp` に `Release` 詳細取得 API が追加され、生成された server / admin の型に反映されていることを確認する
- `GET /admin/v1/releases/{releaseId}` が invalid id で 404、未存在 id で 404、正常系で基本情報と曲順順の収録楽曲一覧を返すことを server 側テストで確認する
- `src/admin/src/server/routes/releases.ts` の `GET /:releaseId` が server API を呼び、401 時の再認証、404 / 422 / 正常時レスポンスの扱いが既存詳細 BFF と同じであることを確認する
- `src/admin/src/pages/releases/[id].astro` と `src/admin/src/components/release/DetailView.tsx` で、基本情報表示、収録楽曲の空状態、一覧からの遷移が成立していることを確認する
- 今回の差分に更新 API、削除 API、収録曲編集 UI、`Song` 詳細への所属 `Release` 表示が含まれていないことを `git diff --name-only` と変更ファイル確認で検証する
