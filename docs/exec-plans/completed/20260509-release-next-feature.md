# Title

Release 作成

## Status

completed

## Background

`docs/exec-plans/completed/20260509-release-list.md` により、`Release` は `search API + BFF + index 画面` まで実装済みになった。一方で `src/admin/src/pages/releases` には `index.astro` しかなく、一覧から新しい `Release` を登録する導線も、登録処理を受ける `create API` も存在しない

`docs/product-specs/20260503-song-media-admin-phase1/models.md` の Phase 1 では、`Release` は「一覧 / 作成 / 詳細」の順で責務が定義されており、作成 / 編集画面で基本情報を入力し、後続で収録楽曲の編集や詳細確認につなげる流れになっている。現状のまま詳細 API だけを先に追加しても新規データを投入できず、一覧画面も閲覧専用のまま止まる

また、`Release` の作成は `Release` 自身の属性に閉じて最小実装しやすく、`TrackEntry` の検索・追加 UI や `Song` 側 read model 反映を後続に分離できる。`1 API + 1 画面` の次の単位として、依存範囲とユーザー価値のバランスが最も良い

## Goal

管理画面から `Release` の基本情報を新規登録できる最小機能として、`Release` 作成 API、admin BFF、作成画面を追加する。これにより一覧の次に必要なデータ投入導線を作り、後続の詳細・更新・収録曲編集の土台を整える

## Scope

- `src/contracts/src/admin/releases` に `Release` 作成 API の request / response を追加する
- `src/server/packages/Release` と `src/server/app/Http` に `Release` 作成 use case と HTTP 入口を追加する
- `src/admin/src/server/routes/releases.ts` に `POST /releases` 相当の BFF を追加する
- `src/admin/src/pages/releases/create/index.astro` と対応する form component を追加する
- 今回の入力項目は `title`、`type`、`distributionType`、`releasedOn`、`description`、`isDisplay` に閉じる

## Non-Scope

- `src/admin/src/pages/releases/[id].astro` の詳細画面追加
- `Release` 更新・削除 API
- 収録楽曲の検索、追加、曲順編集 UI
- `Song` 詳細への所属 `Release` 表示
- `ReleaseSongLink` の保存・取得
- viewer 側の `Release` 表示改善

## Acceptance Criteria

- admin contracts に `Release` 作成 API が定義され、基本情報 6 項目を受け取って作成できる
- server 側に `Release` 作成処理が追加され、収録楽曲なしで `Release` を保存できる
- admin BFF から `Release` 作成 API を呼び出せる
- 管理画面に `Release` 作成画面が追加され、基本情報を入力して保存できる
- 保存成功後に一覧または作成結果を確認できる遷移があり、未実装の詳細 / 収録楽曲編集導線は追加されない

## Steps

1. ✅ `src/contracts/src/admin/releases/service.tsp` と `transport.tsp` を更新し、`POST /releases` の request / response を追加する。`title`、`typeValue`、`distributionTypeValue`、`releasedOn`、`description`、`isDisplay` を受け取り、作成結果として最小の `Release` 表現を返す形に揃える
2. ✅ contract 変更に合わせて `src/contracts/src/admin/admin-users/domain.tsp` と `src/server/packages/AdminUser/Domain/Models/Permission.php` を確認し、`WriteRelease` が未定義なら追加する。続けて生成物更新の対象を計画に含め、server / admin の generated code を `Release` 作成 API に追随させる
3. ✅ `src/server/packages/Release/Application/Admin/UseCase/Create` を追加し、`CreateInputData`、`CreateOutputData`、`CreateUseCase` を実装する。既存の `Song` / `Person` の create use case パターンに合わせ、認可、transaction、repository save、audit log 記録の責務を分離する
4. ✅ `src/server/packages/Release/Domain/Models` と `src/server/packages/Release/Infrastructures/ReleaseRepository.php` を見直し、収録曲なしの `Release` を保存できるようにする。必要なら `Release` の新規生成用 factory / named constructor を追加するが、`TrackEntry` 保存や `Song` 連携は入れない
5. ✅ `src/server/app/Http/Controllers/Api/Admin/V1/Release` と presenter / converter、`src/server/routes/admin.php`、`src/server/packages/Release/Route/ReleaseRouteMap.php`、`src/server/app/Providers/Domain/ReleaseServiceProvider.php` を更新し、`POST /admin/v1/releases` を配線する
6. ✅ `src/admin/src/server/routes/releases.ts` に `POST /releases` の BFF を追加し、生成済み client の `releaseServiceCreateRelease` 相当を呼び出せるようにする。既存 BFF と同じエラー変換・認証更新パターンに合わせる
7. ✅ `src/admin/src/pages/releases/create/index.astro` と `src/admin/src/components/release/CreateForm.tsx` を追加し、基本情報 6 項目の入力と保存を実装する。保存成功後は `/releases` に戻し、一覧から作成画面へ遷移できる導線も `src/admin/src/components/release/SearchList.tsx` か周辺に追加する
8. ✅ `Release` 作成の最小検証を追加して実行する。少なくとも server 側の create use case / feature test と、admin 側の lint 対象を整え、作成後に一覧遷移する基本導線が壊れていないことを確認する

## Decision Log

- 2026-05-09: 一覧の次の 1 単位は詳細ではなく作成にする。新規データ投入ができないと一覧が運用に繋がらず、詳細だけ先にあっても価値が限定的なため
- 2026-05-09: 今回の `Release` 作成は基本情報 6 項目に閉じ、`TrackEntry` の追加や曲順編集は含めない。`1 API + 1 画面` の粒度を維持しつつ、後続の `ReleaseSongLink` 実装と責務を分離するため
- 2026-05-09: save 時の server 実装は既存の `Song` / `Person` の create パターンに寄せ、use case で認可、transaction、audit log を扱う。`Release` だけ別パターンにすると後続の update / delete 実装で一貫性を失うため
- 2026-05-09: 保存成功後の遷移先は未実装の詳細ではなく一覧に戻す。未完成の導線を UI に露出せず、現時点で存在する画面だけで作業を閉じるため
- 2026-05-09: `releasedOn` の不正フォーマットは use case まで届く前に `OpenApiValidator` が 422 で弾く。Feature test では validator の英語メッセージを期待し、use case の日付変換エラーは Integration Test で検証する

## Validation

- `src/contracts/src/admin/releases/service.tsp` と `transport.tsp` に `Release` 作成 API が追加され、生成された server / admin の型に反映されていることを確認する
- `POST /admin/v1/releases` が基本情報 6 項目で `Release` を作成でき、収録楽曲なしでも保存できることを server 側テストで確認する
- `src/admin/src/server/routes/releases.ts` の `POST /releases` が server API を呼び、正常時レスポンスと入力エラーの扱いが既存 create BFF と同じであることを確認する
- `src/admin/src/pages/releases/create/index.astro` と `src/admin/src/components/release/CreateForm.tsx` で、入力、バリデーション表示、保存成功後の `/releases` 遷移が動くことを確認する
- `TrackEntry` 編集、`Song` 連携、`Release` 詳細画面が今回の差分に含まれていないことを `git diff --name-only` と変更ファイル確認で検証する
