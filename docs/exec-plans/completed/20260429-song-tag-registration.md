# Title

楽曲タグ登録機能の実装

## Status

completed

## Background

`src/contracts/src/admin/song-tags/service.tsp` には `createSongTag` 契約が既にあり、`src/admin/src/generated` も `song-tags` のクライアントを持っている。一方で、`src/server/app/Http/Controllers/Api/SongTag` や `src/server/app/Http/Presenters/Api/SongTag` は未実装で、`src/admin/src/server/routes` と `src/admin/src/pages` にも登録導線がない。`src/server/packages/Song` 側には `SongTag` の domain 基盤はあるが、作成に必要な `orderNo` / 保存処理 / create 用 use case はまだ揃っていないため、管理画面から新規登録できない状態になっている。

## Goal

管理画面から楽曲タグを新規作成できるようにし、既存の `createSongTag` 契約に沿ってサーバ API と admin BFF / 画面をつなぐ。

## Scope

- `src/server/packages/Song/Domain/Models/Tag/SongTag.php` を作成用に拡張し、`orderNo` を含むレスポンス前提の形に揃える
- `src/server/packages/Song/Domain/Models/Tag/SongTagFactoryInterface.php`、`SongTagRepositoryInterface.php`、`src/server/packages/Song/Infrastructures/Tag/SongTagFactory.php`、`SongTagRepository.php` に create/save 用の最小実装を追加する
- `src/server/packages/Song/Application/Interactors/Tag` と `src/server/packages/Song/Application/UseCase/Tag/Create` に、楽曲タグ作成用の use case 一式を追加する
- `src/server/app/Http/Controllers/Api/SongTag/CreateSongTagController.php`、`src/server/app/Http/Presenters/Api/SongTag/CreatePresenter.php`、`src/server/app/Http/Presenters/Api/SongTag/Converter.php` を追加する
- `src/server/routes/admin.php` に `song-tags` の create ルートを追加する
- `src/admin/src/server/routes/song-tags.ts` を追加し、作成 API を呼び出せるようにする
- `src/admin/src/pages/song-tags/create/index.astro` と `src/admin/src/components/song-tag/CreateForm.tsx` を追加し、成功メッセージを見える形で返す

## Non-Scope

- `src/contracts/src/admin/song-tags` の契約変更
- `song-tags` の一覧・検索・詳細・更新・削除機能
- `song-tags` 用の DB スキーマ変更やマイグレーション追加
- `src/server/app/Models/Song/SongTag.php` の再設計
- `src/server/app/Providers/Domain/SongServiceProvider.php` 以外の DI 構成の再編成

## Acceptance Criteria

- 管理者が `POST /admin/v1/song-tags` を送ると、`createSongTag` 契約に沿った成功レスポンスまたは入力エラーが返る
- `src/admin/src/pages/song-tags/create/index.astro` から楽曲タグを新規登録でき、成功時に画面上で完了が分かる
- 追加した server 側の controller / presenter / use case と admin 側の BFF / 画面が、既存の `createSongTag` 契約を前提に接続されている
- 今回の変更に `src/contracts/src/admin/song-tags` の契約修正は含まれていない

## Steps

1. ✅ `SongTag` の domain 基盤を作成用に揃える。`src/server/packages/Song/Domain/Models/Tag/SongTag.php` に `orderNo` を追加し、`SongTagFactoryInterface` / `SongTagRepositoryInterface` を create 用の最小契約に拡張する。あわせて `src/server/packages/Song/Infrastructures/Tag/SongTagFactory.php` / `SongTagRepository.php` を実装し、`getMaxOrderNo()` と `save()`、必要なら `findByName()` を提供できるようにする。
2. ✅ 作成時の整合性チェックをまとめる `SongTag` 用の domain service を追加する。`SongTagName` の入力検証、重複名チェック、`orderNo = max + 10` の採番をこの層に寄せ、後続の use case からは「作成に必要な検証済みの `SongTag` を受け取る」形にする。
3. ✅ `src/server/packages/Song/Application/UseCase/Tag/Create` と `src/server/packages/Song/Application/Interactors/Tag/CreateInteractor.php` を追加する。`AuthContext` と権限チェック、transaction、domain service 呼び出し、repository 保存、`Result<...>` への error mapping までを Creator / Song の既存パターンに合わせて実装する。
4. ✅ `src/server/app/Http/Controllers/Api/SongTag/CreateSongTagController.php`、`src/server/app/Http/Presenters/Api/SongTag/CreatePresenter.php`、`src/server/app/Http/Presenters/Api/SongTag/Converter.php` を追加する。`SongTagCreateResponse` に `SongTag` を詰めて返し、validation / business error は既存の `ResolvesUseCaseError` に乗せる。
5. ✅ `src/server/routes/admin.php` に `POST /admin/v1/song-tags` を追加し、`SongTagRouteMap::Create` に結びつける。`OpenApiValidator` と `Authenticate` の適用位置は既存の `creators` / `songs` と同じ構造に揃える。
6. ✅ `src/admin/src/server/routes/song-tags.ts` を追加し、`songTagServiceCreateSongTag` を呼ぶ BFF を用意する。入力は `name` のみとし、`resolveApiResponse` と `authGuard` は既存ルートと同じ流れを再利用する。
7. ✅ `src/admin/src/pages/song-tags/create/index.astro` と `src/admin/src/components/song-tag/CreateForm.tsx` を追加する。成功後は `setFlash` で結果を保持し、`FlashMessage` を create page 側に載せて `/song-tags/create` へ戻したときに完了が見えるようにする。
8. ✅ `mise run phpstan` と `bunx tsc --noEmit` 相当の検証を前提に、server 側は controller / presenter / use case / domain、admin 側は BFF / page / component の結合点だけを重点確認する。

## Decision Log

- 2026-04-29: `song-tags` は既存の `Song` package に集約し、専用 package や追加の service provider は作らない。既存の `Song` / `Creator` の分離方針に合わせて HTTP 層だけ `SongTag` 名前空間で切る。
- 2026-04-29: `createSongTag` 契約と生成済み admin client は既にあるため、契約変更と再生成は行わない。今回の作業は実装接続に限定する。
- 2026-04-29: `SongTag` のレスポンスには `orderNo` が必要なので、domain model と presenter でこの値を返す前提に揃える。DB 側の既存カラムをそのまま利用する。
- 2026-04-29: 作成後の成功表示は list 画面への遷移ではなく、`/song-tags/create` へ戻して `FlashMessage` を出す。`song-tags` の一覧ページはこのタスクの範囲に含めない。
- 2026-04-29: 永続化は Creator と同じく repository / factory 経由に統一し、controller から Eloquent model を直接触らない。
- 2026-04-29: `SongTag` domain model 自体は実装前から `orderNo` と `toArray()` を持っていたため、Step 1 は create 向け interface / repository / factory の追加に絞って進めた。

## Validation

- `mise run phpstan` で `src/server/packages/Song`、`src/server/app/Http/Controllers/Api/SongTag`、`src/server/app/Http/Presenters/Api/SongTag`、`src/server/routes/admin.php` の静的解析が通ることを確認する。
- `bunx tsc --noEmit` で `src/admin/src/server/routes/song-tags.ts`、`src/admin/src/components/song-tag/CreateForm.tsx`、`src/admin/src/pages/song-tags/create/index.astro` の型エラーがないことを確認する。
- ブラウザまたは API で `POST /admin/v1/song-tags` を送信し、成功時に `/song-tags/create` 上で完了メッセージが表示されること、失敗時に入力エラーが `name` に紐づくことを確認する。
- 変更差分を確認し、今回の編集が `src/server/packages/Song`、`src/server/app/Http/Controllers/Api/SongTag`、`src/server/app/Http/Presenters/Api/SongTag`、`src/server/routes/admin.php`、`src/admin/src/server/routes/song-tags.ts`、`src/admin/src/pages/song-tags/create/index.astro`、`src/admin/src/components/song-tag/CreateForm.tsx`、および計画書に閉じていることを確認する。
