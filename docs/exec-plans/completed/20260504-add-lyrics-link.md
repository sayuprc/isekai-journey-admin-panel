## Title

楽曲への歌詞リンク追加

## Status

completed

## Background

楽曲は現在 `title` や `description`、作詞者などは持っていますが、歌詞への外部リンクを保持していません。歌詞リンクは楽曲ごとに未設定の可能性があるため、空文字ではなく Optional な値として契約と実装で扱う必要があります

## Goal

楽曲に歌詞リンクを追加し、未設定時は Optional として保存・取得・更新できる状態にする

## Scope

- `src/contracts/src/admin/songs/domain.tsp` と `transport.tsp` で Song の入出力に歌詞リンクを追加し、未設定を表現できる契約にする
- `src/server/database/atlas/schemas/songs.my.hcl`、`src/server/app/Models/Song/Song.php`、`src/server/packages/Song/*` で歌詞リンクを永続化し、集約・組み立て・Presenter に反映する
- `src/admin/src/components/song/CreateForm.tsx` と `EditableForm.tsx` で歌詞リンクを編集可能にする
- `src/server/tests/Feature/Api/Song/*` など楽曲 API の既存テストで、設定あり・未設定の両方を検証する

## Non-Scope

- 閲覧サイト `src/viewer` への表示追加
- 歌詞リンク先の疎通確認や外部サービス連携
- 楽曲以外のエンティティへのリンク項目追加

## Acceptance Criteria

- 楽曲作成・更新 API が歌詞リンクを受け取り、未設定時は Optional として扱える
- 楽曲取得 API が、設定済みなら歌詞リンクを返し、未設定なら未設定として判別できる形で返す
- 管理画面の楽曲作成・編集画面で歌詞リンクを入力・未入力の両方で保存できる
- 楽曲 API のテストに、歌詞リンクありと未設定のケースが追加されて通る

## Steps

1. ✅ `src/contracts/src/admin/songs/domain.tsp` と `src/contracts/src/admin/songs/transport.tsp` に歌詞リンクの Optional フィールドを追加し、作成・更新・取得レスポンスで未設定を `null` 相当として表現できる契約にする
2. ✅ `mise run generate:server` と `mise run generate:client:admin` を実行し、`src/server/Generated/*` と `src/admin/src/generated/*` の Song 関連モデル/クライアントを更新する
3. ✅ `src/server/database/atlas/schemas/songs.my.hcl` に nullable な歌詞リンク列を追加し、`src/server/app/Models/Song/Song.php` のプロパティ定義へ反映して Eloquent から参照できる状態にする
4. ✅ `src/server/packages/Song/Domain/Models/Song.php`、`src/server/packages/Song/Domain/Services/SongIntegrityService.php`、必要な ValueObject 群に歌詞リンクの Optional 値を追加し、空文字をそのまま保持せず未設定へ正規化したうえで `Song` を生成できるようにする
5. ✅ `src/server/packages/Song/Application/UseCase/Create/CreateInputData.php`、`src/server/packages/Song/Application/UseCase/Update/UpdateInputData.php`、`CreateUseCase.php`、`UpdateUseCase.php` を更新し、HTTP 入力から歌詞リンクを受け取りドメインサービスへ渡す経路を追加する
6. ✅ `src/server/packages/Song/Infrastructures/SongRepository.php`、`src/server/packages/Song/Application/Assemble/AssembledSong.php`、`SongAssembler.php`、`src/server/app/Http/Presenters/Api/Song/Converter.php` を更新し、DB の nullable 値をドメイン・組み立て・OpenAPI レスポンスへ一貫して伝播させる
7. ✅ `src/admin/src/components/song/CreateForm.tsx` と `src/admin/src/components/song/EditableForm.tsx` に歌詞リンク入力欄を追加し、未入力時は空文字ではなく `null` として API に送るよう送信処理を調整する
8. ✅ `src/server/tests/Support/Domain/EntityFactory.php` と Song 関連の Unit/Integration/Feature テストで歌詞リンク引数を扱えるようにし、既存テストの組み立てヘルパーを壊さず更新する
9. ✅ `src/server/tests/Feature/Api/Song/CreateSongTest.php`、`GetSongTest.php`、`UpdateSongTest.php` を中心に、歌詞リンクあり/未設定の両ケースを追加して Acceptance Criteria を検証する

## Decision Log

- 2026-05-04: 歌詞リンクは `description` と同じ必須文字列にはせず、契約・DB・ドメインで nullable/Optional として扱う。未設定を空文字で表すと API 利用者が「未設定」と「空文字」を区別できず、今回の要件を満たせないため
- 2026-05-04: Optional の正規化は管理画面ではなくサーバー側ドメイン境界で行う。フロントの入力揺れに依存せず、他の入力経路が増えても未設定表現を一貫させるため
- 2026-05-04: 取得 API の表現は Song 詳細レスポンスに歌詞リンクフィールドを常に含め、未設定時は `null` を返す前提で進める。フィールド欠落よりクライアントの型と分岐が安定し、Optional 契約として扱いやすいため
- 2026-05-04: 変更範囲は Song の詳細系に限定し、一覧用 `SongSummary` と viewer 表示には広げない。今回の要件達成に不要な波及を避け、影響を最小化するため
- 2026-05-04: Step 2 の生成コマンドは計画書記載の `generate:server` / `generate:client:admin` ではなく、現行 `mise` タスクの `api:generate` / `admin:generate` を使う。リポジトリの実タスク定義が変更されており、同等目的を現行コマンドで満たすため

## Validation

- `mise run generate:server` と `mise run generate:client:admin` が成功し、生成された Song モデル/クライアントに歌詞リンクの Optional フィールドが反映されることを確認する
- 楽曲作成 API テストで、歌詞リンクを指定した作成結果に値が含まれること、未指定の作成結果で `null` が返ることを確認する
- 楽曲取得 API テストで、DB に歌詞リンクがある楽曲は値を返し、`NULL` の楽曲は `null` を返すことを確認する
- 楽曲更新 API テストで、既存値を別 URL に更新できることと、入力を外して未設定に戻せることを確認する
- 必要に応じて Song ドメイン/Repository/Assembler のテストを実行し、nullable 値の保存・復元・レスポンス変換で回帰がないことを確認する
