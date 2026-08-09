# 楽曲で使用中の楽曲タグ削除を防止する

## Title

使用中楽曲タグの削除防止

## Status

completed

## Background

楽曲タグ削除 API は現在、認証・権限・ID 形式を確認したあと `SongTagRepository::delete()` を呼び出すだけで、対象タグが `song_taggings` で楽曲に紐づいているかを事前に判定していない。DB スキーマ上は `song_taggings.song_tag_id` から `song_tags.song_tag_id` へ `RESTRICT` 外部キーがあるため、使用中タグを削除しようとするとアプリケーションの業務エラーではなく DB 制約エラーに依存する状態になっている。

## Goal

楽曲に紐づいている楽曲タグを削除しようとした場合、削除せずに業務エラーとして扱う。未使用の楽曲タグは従来どおり削除できる。

## Scope

- `src/server/packages/Song/Application/Interactors/Tag/DeleteInteractor.php`
- `src/server/packages/Song/Domain/Models/Tag/SongTagRepositoryInterface.php`
- `src/server/packages/Song/Infrastructures/Tag/SongTagRepository.php`
- `src/server/app/Models/Song/SongTagging.php` の使用確認
- `src/server/app/Http/Presenters/Api/SongTag/DeletePresenter.php` と共通エラー変換の既存挙動確認
- `src/server/tests/Unit/Song/Application/Interactors/Tag/DeleteInteractorTest.php`
- `src/server/tests/Integration/Song/Infrastructures/Tag/SongTagRepositoryTest.php`
- `src/server/tests/Feature/Api/SongTag/DeleteSongTagTest.php`

## Non-Scope

- 楽曲タグ作成・更新・検索・一覧の仕様変更
- 楽曲編集時のタグ紐づけ仕様変更
- DB スキーマの外部キー制約変更
- 管理画面 UI の削除ボタン表示制御や文言変更
- OpenAPI / TypeSpec のレスポンス型変更（削除 API はすでに `BadRequest` を含む）

## Acceptance Criteria

- 使用中の楽曲タグ ID で `DELETE /song-tags/{songTagId}` を呼ぶと、HTTP 400 と `{ "message": "この楽曲タグは楽曲に使用されているため削除できません" }` が返り、`song_tags` の対象レコードが残る。
- 使用中の楽曲タグ ID で削除 UseCase を実行すると、Repository の `delete()` は呼ばれず、`BusinessLogicError` が返る。
- 未使用の楽曲タグ ID で削除 API / UseCase を実行すると、従来どおり HTTP 204 / Ok になり、対象レコードが削除される。
- 存在しないが形式が正しい楽曲タグ ID の削除は、従来どおり HTTP 204 を返す。
- 不正な楽曲タグ ID、未認証、権限不足の既存エラー挙動は変わらない。

## Steps

1. ✅ `src/server/packages/Song/Domain/Models/Tag/SongTagRepositoryInterface.php` に `isUsed(SongTagId $songTagId): bool` を追加し、削除前に `song_taggings` の参照有無を問い合わせる Repository 契約を明示する。
2. ✅ `src/server/packages/Song/Infrastructures/Tag/SongTagRepository.php` に `App\Models\Song\SongTagging` を import し、`isUsed()` で `song_tag_id` を UUID バイナリへ変換して `SongTagging::query()->where('song_tag_id', ...)->exists()` を返す実装を追加する。これは Step 1 の契約追加に依存する。
3. ✅ `src/server/packages/Song/Application/Interactors/Tag/DeleteInteractor.php` に `BusinessLogicError` の import を追加し、`SongTagId::create()` 成功後に `repository->isUsed($songTagId)` を確認する。使用中なら `Err(new BusinessLogicError('この楽曲タグは楽曲に使用されているため削除できません'))` を返し、`delete()` を呼ばない。未使用なら従来どおり `delete()` 後に `Ok(null)` を返す。これは Step 1, 2 に依存する。
4. ✅ `src/server/tests/Unit/Song/Application/Interactors/Tag/DeleteInteractorTest.php` を更新し、正常削除では `isUsed()` が `false` を返してから `delete()` が呼ばれること、使用中では `isUsed()` が `true` を返して `BusinessLogicError` のメッセージが一致し `delete()` が呼ばれないことを追加する。認可エラーと不正 ID のケースでは `isUsed()` と `delete()` が呼ばれないことも明示する。
5. ✅ `src/server/tests/Integration/Song/Infrastructures/Tag/SongTagRepositoryTest.php` に `isUsed()` の検証を追加する。`storeSongTags()` でタグを保存し、`SongRepository::save()` または `storeSongs()` で対象タグを持つ楽曲を保存した場合は `true`、紐づきがないタグまたは存在しない形式正しい ID は `false` になることを確認する。これは Step 2 に依存する。
6. ✅ `src/server/tests/Feature/Api/SongTag/DeleteSongTagTest.php` に使用中タグ削除の API テストを追加する。タグとそのタグを参照する楽曲を保存してから `DELETE /song-tags/{songTagId}` を呼び、HTTP 400 と `{ "message": "この楽曲タグは楽曲に使用されているため削除できません" }` を検証し、`SongTagRepository::find()` で対象タグが残ることを確認する。
7. ✅ `src/server/tests/Feature/Api/SongTag/DeleteSongTagTest.php` の既存テストを必要最小限で補強し、未使用タグ削除は HTTP 204 とレコード削除、存在しない形式正しい ID は HTTP 204、未認証は HTTP 401 のまま変わらないことを維持する。権限不足と不正 ID は既存テスト範囲にないため、UseCase 単体テストで挙動固定し、API 側は今回の変更で Presenter/Route を変更しない。
8. ✅ `src/server/app/Http/Presenters/Api/SongTag/DeletePresenter.php` と `src/server/app/Http/Presenters/Api/Support/ResolvesUseCaseError.php` は変更せず、`BusinessLogicError` が既存の共通変換で HTTP 400 + `message` になることを前提にする。`src/server/app/Providers/Domain/SongServiceProvider.php` も、新規サービスを追加しないため変更しない。

## Decision Log

- 2026-05-01: 使用判定は新規サービスではなく `SongTagRepositoryInterface::isUsed()` として追加する。対象の参照元は同じ Song ドメイン内の `song_taggings` に限定され、`DeleteInteractor` が既に `SongTagRepositoryInterface` に依存しているため、依存追加を最小化できる。
- 2026-05-01: `CreatorUsageChecker` と同様に削除前の業務ルールとして `BusinessLogicError` を返す。`ResolvesUseCaseError` が `BusinessLogicError` を HTTP 400 + `ErrorResponse.message` に変換済みなので、Presenter と OpenAPI / TypeSpec は変更しない。
- 2026-05-01: 存在しないタグ ID は従来どおり 204 とする。`isUsed()` は `song_taggings` の存在だけを見るため、未存在 ID でも `false` になり、その後の `delete()` は no-op になる。
- 2026-05-01: `mise run test` は `--filter` ではなく path 引数を受け取るタスクだったため、Validation の対象テストはファイル path 指定で実行する。

## Validation

- `mise run test --filter='Tests\\Unit\\Song\\Application\\Interactors\\Tag\\DeleteInteractorTest'`
- `mise run test --filter='Tests\\Integration\\Song\\Infrastructures\\Tag\\SongTagRepositoryTest'`
- `mise run test --filter='Tests\\Feature\\Api\\SongTag\\DeleteSongTagTest'`
- `mise run phpstan`
- `mise run ecs`

実行結果:

- `mise run test tests/Unit/Song/Application/Interactors/Tag/DeleteInteractorTest.php`: OK
- `mise run test tests/Integration/Song/Infrastructures/Tag/SongTagRepositoryTest.php`: OK
- `mise run test tests/Feature/Api/SongTag/DeleteSongTagTest.php`: OK
- `mise run phpstan`: OK
- `mise run ecs`: OK
- `git diff --check`: OK
