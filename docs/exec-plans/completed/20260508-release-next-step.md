# Title

Release サーバー実行境界

## Status

completed

## Background

`docs/exec-plans/completed/20260508-release-foundation.md` により、`Release` / `TrackEntry` の Atlas schema、Eloquent Model、pure domain model は追加済みになった。一方で現状の `src/server/packages/Release` には repository interface / implementation と DI 設定がまだなく、後続の個別実装が依存できる server 側の永続化境界が存在しない。

この状態のまま contracts や admin、個別 use case 実装に進むと、`TrackEntry` をどの repository で保存・復元するかが server 側で先に固定されていないうえ、`Release` API の契約ファイルや admin BFF の置き場もまだないため、並列開発時の手戻りが増える。後続の create / update / get / search / delete 実装が同じ repository / DI、TypeSpec の入口、BFF の入口に依存できるよう、共通の永続化境界と器だけを先に揃える必要がある。

また、前提として `Song` は pure domain / ORM ともに `Release` / `TrackEntry` を持たず、`TrackEntry` は `Release` 側だけで扱う。この責務境界を崩さずに、次の user-facing タスクである `Release` CRUD と収録曲保存の server 依存先を先に用意する必要がある。

## Goal

`Release` 集約の永続化に必要な最小 server 境界と、contracts / BFF の器だけを追加し、後続の use case 実装、HTTP、admin、`Song` 詳細への所属 `Release` 表示が依存できる stable な土台を整える。

## Scope

- `src/server/packages/Release/Domain/Models` に `ReleaseRepositoryInterface` など永続化境界の型を追加する
- `src/server/packages/Release/Infrastructures` に、Eloquent を使った repository を追加する
- `src/server/app/Providers/Domain` に `ReleaseServiceProvider` を追加し、repository の DI を束ねる
- `src/contracts/src/admin/releases` に `main.tsp` と必要最小限の `domain.tsp` を追加し、`src/contracts/src/admin/main.tsp` から参照できる器だけを作る
- `src/admin` に `Release` 向け BFF / route の最小ディレクトリと空の入口を追加し、後続実装の器だけを作る
- 必要であれば `src/server/packages/AdminUser/Domain/Models/Permission.php` に `Release` 用権限を追加する

## Non-Scope

- OpenAPI や generated code の更新
- `Release` API の request / response の詳細定義
- `src/server/app/Http` の controller / presenter / request 実装
- `src/server/packages/Release/Route` の追加
- `src/server/packages/Release/Application/Admin/UseCase` の実装
- `src/server/packages/Release/Application/Admin/Query` の実装
- `src/server/packages/Release/Domain/Services` の追加
- `src/admin` の BFF ロジック、画面、フォーム実装
- `Song` 詳細取得時に所属 `Release` を組み立てる処理の追加
- `src/server/app/Models/Song` や `src/server/packages/Song/Domain/Models/Song.php` への relation / property 追加
- `Release` の schema 追加変更や viewer 反映

## Acceptance Criteria

- `Release` package に repository interface / implementation と service provider が揃い、後続の use case 実装が依存できる永続化境界が定義されている
- `TrackEntry` の保存・復元は `Release` repository の責務として扱われ、`Song` 側の pure domain / ORM には `Release` / `TrackEntry` が追加されていない
- `src/contracts/src/admin/releases` に `main.tsp` と必要最小限の `domain.tsp` が追加され、`src/contracts/src/admin/main.tsp` から参照できるが、request / response 詳細や generated code にはまだ踏み込んでいない
- `src/admin` に `Release` 用の BFF / route の器だけが追加され、具体的な fetch や画面ロジックはまだ入っていない
- 変更が `src/server/packages/Release`、`src/server/app/Providers/Domain`、必要最小限の `src/server/packages/AdminUser`、`src/contracts`、`src/admin` の器に閉じており、`src/server/app/Http` と `Song` package には差分が入っていない

## Steps

1. ✅ `src/server/packages/Release/Domain/Models` を確認し、`Release.php` / `TrackEntry.php` / `TrackEntries.php` に合わせて `ReleaseRepositoryInterface.php` の責務を定義する。保存・取得の対象は `Release` 集約全体とし、`Song` 側の model / relation は触らない前提を interface 上でも固定する。
2. ✅ `src/server/app/Models/Release/Release.php` と `src/server/app/Models/Release/TrackEntry.php` を参照し、`src/server/packages/Release/Infrastructures/ReleaseRepository.php` を追加する。`TrackEntry` の保存・復元を `Release` repository に閉じ込め、後続 use case から呼べる最小実装だけを置く。
3. ✅ `src/server/app/Providers/Domain/MediaServiceProvider.php` を踏まえ、`src/server/app/Providers/Domain/ReleaseServiceProvider.php` を追加する。`ReleaseRepositoryInterface` と `ReleaseRepository` の bind だけを定義し、query / use case / domain service の bind は入れない。
4. ✅ `src/server/bootstrap/providers.php` など provider 集約地点を確認し、`ReleaseServiceProvider` を server 起動時に読み込むための最小差分を追加する。既存 provider の順序や他ドメインの登録方法は崩さない。
5. ✅ `src/server/packages/AdminUser/Domain/Models/Permission.php` の既存命名を確認し、`Release` の BFF 入口を追加するために権限制御上の器が必要な場合のみ `ReadRelease` / `WriteRelease` を追加する。不要ならこのファイルは変更しない判断を記録する。
6. ✅ `src/contracts/src/admin/media/main.tsp` / `domain.tsp` と `src/contracts/src/admin/main.tsp` を参照し、`src/contracts/src/admin/releases/main.tsp` と必要最小限の `domain.tsp` を追加する。ここでは `service.tsp` / `transport.tsp` は作らず、`main.tsp` から `domain.tsp` を読む器と `admin/main.tsp` からの import だけに留める。
7. ✅ `src/admin/src/server/routes/media.ts` と `src/admin/src/server/index.ts` の集約方法に合わせて、`src/admin/src/server/routes/releases.ts` を追加し、`src/admin/src/server/index.ts` に登録する。BFF の中身は空の `Elysia` route か stub export のみとし、generated client 呼び出しや fetch ロジックは入れない。
8. ✅ `git diff --name-only` と `rg` で変更範囲を確認し、`src/server/app/Http`、`src/server/packages/Release/Application`、`src/contracts/src/admin/releases/service.tsp` / `transport.tsp`、`Song` package / model に差分がないことを検証する。必要なら構文確認だけを追加し、生成や実通信は行わない。

## Decision Log

- 2026-05-08: 次の最小タスクは `Release` の contracts や admin ではなく、server 内の repository / DI 境界を揃えることにする。後続の個別 use case 実装と API 契約、UI が安定した依存先を共有でき、並列開発時の衝突を減らせるため。
- 2026-05-08: `TrackEntry` は `Release` 側の集約責務として保存・復元し、`Song` 側には relation や domain property を追加しない。前回固定した責務境界を維持するため。
- 2026-05-08: use case、query、domain service は共通基盤としては作らず、後続の各実装で個別に組み立てる。今回のタスクは再利用される永続化境界だけに絞って変更衝突を減らすため。
- 2026-05-08: TypeSpec は実装詳細まで入れず、`releases` 用の器だけを追加する。server 側の repository 基盤と並列で contract の入口を固定しつつ、request / response 形状の合意を別タスクへ分離するため。
- 2026-05-08: admin BFF も実装までは入れず、`Release` 用の route / module の器だけを追加する。契約と同様に入口だけを先に揃え、具体的な fetch や画面導線は別タスクへ分離するため。
- 2026-05-08: `src/contracts/src/admin/releases` は既存の 4 ファイル構成を踏襲せず、今回は `main.tsp` と `domain.tsp` だけを作る。`service.tsp` / `transport.tsp` を先に作ると未確定の API 詳細を契約に仮置きしてしまうため。
- 2026-05-08: admin の `releases` route は generated client 非依存の stub に留める。まだ contracts generate を回さない前提なので、BFF 側から先に具体ロジックを持たせないため。
- 2026-05-08: `Release` 用権限は今回追加しない。BFF は空の入口だけで認可分岐をまだ持たず、権限値の導入は実際の route 実装と同時に行う方が衝突を減らせるため。

## Validation

- `src/server/packages/Release` に、repository / infrastructure 構成が揃っていることを確認する
- `rg -n "namespace Release|ReleaseRepositoryInterface|ReleaseServiceProvider" src/server/packages/Release src/server/app/Providers/Domain` で、新規境界が期待した配置にあることを確認する
- `src/contracts/src/admin/releases/main.tsp` と必要最小限の `domain.tsp` が存在し、`src/contracts/src/admin/main.tsp` から参照されていることを確認する。`src/contracts/src/admin/releases/service.tsp` と `transport.tsp` は今回存在しないままであることも確認する
- `src/admin` に `Release` 用の BFF / route の器だけが追加され、generated client 呼び出し、fetch、画面遷移、フォーム処理などの実装ロジックがまだ入っていないことを確認する
- `git diff --name-only` で、変更が `src/server/packages/Release`、`src/server/app/Providers/Domain`、必要最小限の `src/server/packages/AdminUser`、`src/contracts`、`src/admin` の器に閉じていることを確認する
- `src/server/app/Models/Song` と `src/server/packages/Song/Domain/Models/Song.php` に差分がなく、`Song` が `Release` / `TrackEntry` を持たない前提が維持されていることを確認する
- `src/server/app/Http` と `src/server/packages/Release/Application` に差分がなく、今回のタスクが repository / DI / contracts / BFF の器だけに留まっていることを確認する
