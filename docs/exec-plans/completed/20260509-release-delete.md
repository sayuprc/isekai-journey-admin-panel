# Title

Release 削除

## Status

completed

## Background

`Release` は `docs/exec-plans/completed/20260509-release-list.md`、`20260509-release-next-feature.md`、`20260509-release-detail.md`、`20260509-release-update.md` により、一覧・作成・詳細・更新まで completed になった。一方で `src/contracts/src/admin/releases/service.tsp` には delete API がなく、`src/admin/src/server/routes/releases.ts` にも削除 BFF が存在しないため、保存済み `Release` を管理画面から取り除けない

Phase 1 では `Release CRUD` が必要で、既存の `Person` / `Media` / `Song` は delete API と UI 導線を持っている。`Release` も `src/admin/src/components/release/DetailView.tsx` に update UI が揃っているため、次はこの詳細画面に削除導線を足して CRUD を閉じるのが自然

## Goal

管理画面の `Release` 詳細画面から既存 `Release` を削除できる最小機能として、`DELETE /releases/{releaseId}`、admin BFF、詳細画面の削除導線を追加する

## Scope

- `src/contracts/src/admin/releases/service.tsp` に `DELETE /releases/{releaseId}` を追加し、admin contracts / generated に反映する
- `src/server/packages/Release/Domain/Models/ReleaseRepositoryInterface.php` と `src/server/packages/Release/Infrastructures/ReleaseRepository.php` に `Release` 削除処理を追加する
- `src/server/packages/Release/Application/Admin/UseCase/Delete`、`src/server/app/Http/Controllers/Api/Admin/V1/Release`、presenter、`src/server/packages/Release/Route/ReleaseRouteMap.php`、`src/server/routes/admin.php` に削除 API の server 入口を追加する
- `src/admin/src/server/routes/releases.ts` に `DELETE /releases/:releaseId` 相当の BFF を追加する
- `src/admin/src/components/release/DetailView.tsx` と `src/admin/src/pages/releases/[id].astro` に削除ボタンと確認導線を追加し、成功後は `/releases` 一覧へ戻す

## Non-Scope

- `Release` 一覧画面からの一括削除
- `Release` の論理削除や復元機能
- `Song` 詳細への所属 `Release` 表示
- `Release` 検索条件や詳細取得 response の変更
- viewer 側の `Release` 表示改善

## Acceptance Criteria

- admin contracts に `DELETE /releases/{releaseId}` が定義され、server / admin の生成物が更新されている
- server 側に `Release` 削除 use case と HTTP 入口が追加され、存在しない `releaseId` を含めて既存 delete 実装と整合するレスポンスで削除できる
- admin BFF から `Release` 削除 API を呼び出せる
- 管理画面の `src/admin/src/pages/releases/[id].astro` で削除導線が表示され、確認後に削除を実行できる
- 削除成功後は `/releases` 一覧へ戻り、削除済み `Release` が詳細取得・一覧表示の対象から外れることを確認できる

## Steps

1. ✅ `src/contracts/src/admin/releases/service.tsp` に `@delete deleteRelease(@path releaseId: uuid)` を追加し、response は既存 delete API に揃えて `NoContent | Unauthorized | Forbidden | NotFound | Unprocessable | ServerError` を返す形にする
2. ✅ contract 変更に追随して `src/contracts/generated/oas/IsekaiObservatory.Admin.v1.yaml`、`src/server/Generated/lib/Api/ReleaseApi.php`、`src/admin/src/generated/*` を再生成し、admin / server の client・route 生成物へ delete を通す
3. ✅ `src/server/packages/Release/Domain/Models/ReleaseRepositoryInterface.php` に `delete(ReleaseId $releaseId): void` を追加し、`src/server/packages/Release/Infrastructures/ReleaseRepository.php` で `releases` の削除を実装する。削除対象の `trackEntries` は schema の `ON DELETE CASCADE` に委ね、repository で二重削除は足さない
4. ✅ `src/server/packages/Release/Application/Admin/UseCase/Delete` を追加し、`Permission::WriteRelease`、`TransactionInterface`、`ReleaseRepositoryInterface::find()`、audit log を使った delete 処理を実装する。未存在 `releaseId` の扱いは `Song` delete ではなく `Media` delete に合わせて 204 に寄せるか、既存 `Release` の AC と API contract に合わせて 404 にするかを確認し、選んだ方へ統一する
5. ✅ `src/server/app/Http/Controllers/Api/Admin/V1/Release/DeleteReleaseController.php`、`DeletePresenter.php`、`src/server/packages/Release/Route/ReleaseRouteMap.php`、`src/server/routes/admin.php` を追加・更新し、`DELETE /admin/v1/releases/{releaseId}` を配線する
6. ✅ `src/admin/src/server/routes/releases.ts` に `DELETE /releases/:releaseId` を追加し、生成 client の `releaseServiceDeleteRelease` を `withAuthRetry` / `resolveApiResponse` パターンで呼ぶ。params schema は `releaseId: string` のみとする
7. ✅ `src/admin/src/components/release/DetailView.tsx` に削除ボタンと確認導線を追加し、更新フォームと干渉しないよう送信 state を分けて扱う。削除成功時は flash を出して `/releases` へ戻し、404 / 422 は既存 detail / update のエラーハンドリングに揃える
8. ✅ `src/server/tests/Feature/Api/Admin/V1/Release/DeleteReleaseTest.php` と必要なら integration test を追加し、正常系・権限不足・invalid id・未存在 id・`trackEntries` を持つ `Release` の削除を検証する。あわせて `mise run contract:format:check`、`mise run api:phpstan ...Release...`、`mise run admin:lint src/pages/releases/[id].astro src/components/release src/server/routes/releases.ts` を通す

## Decision Log

- 2026-05-09: `Release` 削除は `DELETE /releases/{releaseId}` と詳細画面の削除導線だけに閉じる。CRUD の最後の 1 単位として完結させ、一覧一括削除や `Song` 側表示には広げない
- 2026-05-09: `release_track_entries` は `release_id` 外部キーが `ON DELETE CASCADE` のため、`ReleaseRepository` では `Release` 本体だけを削除し、収録曲関連の明示 delete は追加しない。schema の責務に合わせた方が実装が単純で、更新時の relation 保存ロジックとも衝突しないため
- 2026-05-09: admin UI の削除導線は `src/admin/src/components/release/DetailView.tsx` に追加する。既に `Release` 編集の source of truth がこの画面に集約されており、別画面や一覧側に導線を増やすより変更範囲が小さいため
- 2026-05-09: 削除成功後は `/releases` 一覧へ戻す。既存の `Media` / `Song` 系の操作感に揃えつつ、削除済み詳細へ留まる不整合を避けられるため

## Validation

- `mise run contract:format:check`
- `docker compose exec php php artisan test tests/Feature/Api/Admin/V1/Release/DeleteReleaseTest.php`
- 必要に応じて `docker compose exec php php artisan test tests/Integration/Release/Application/Admin/UseCase/Delete`
- `mise run api:phpstan packages/Release app/Http/Controllers/Api/Admin/V1/Release app/Http/Presenters/Api/Admin/V1/Release tests/Feature/Api/Admin/V1/Release`
- `mise run admin:lint src/pages/releases/[id].astro src/components/release src/server/routes/releases.ts`
- 管理画面で `trackEntries` を持つ `Release` を削除し、一覧へ戻った後に同一 `releaseId` の詳細取得が失敗することを確認する
- `git diff --name-only` で update UI の拡張や `Song` 側表示変更が差分に入っていないことを確認する
