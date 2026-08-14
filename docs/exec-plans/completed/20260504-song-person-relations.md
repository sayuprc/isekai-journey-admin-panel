# Title

Song Person 関係統合

## Status

completed

## Background

現在の楽曲と人物の関係は `song_lyricists` / `song_composers` / `song_arrangers` の 3 テーブルと 3 種の配列契約で分かれている。`Person` 基盤の上でこれを 1 テーブルと `role` Enum へ畳むことで、今後の役割追加と実装保守を単純化する

## Goal

`feature/person-song-relations` で楽曲と人物の紐づきを `song-persons` に統合し、songs 契約・server 実装・テストを新モデルへ切り替える

## Scope

- `src/contracts/src/admin/songs`
- `src/server/database/atlas/schemas/song-persons.my.hcl`
- `src/server/database/atlas/schemas/song-{lyricists,composers,arrangers}.my.hcl`
- `src/server/packages/Song/Domain/Models`
- `src/server/packages/Song/Domain/Services/SongIntegrityService.php`
- `src/server/packages/Song/Application`
- `src/server/packages/Song/Infrastructures`
- `src/server/packages/Support/Infrastructures/Mapper.php`
- `src/server/tests/Feature/Api/Song`
- `src/server/tests/Integration/Song`
- `src/server/tests/Unit/Song`

## Non-Scope

- `Creator` / `Performer` の削除
- admin の `/persons` 画面追加
- admin の creators/performers 画面削除

## Acceptance Criteria

- 楽曲と人物の紐づきが `song-persons` の 1 テーブルになっている
- songs 契約が `personId` と `role` を持つ単一配列構造へ更新されている
- 楽曲の作成・更新・取得が、同一人物に複数 role を持たせた状態で通る

## Steps

1. `src/contracts/src/admin/songs/{domain,transport}.tsp` を `personId + role + orderNo` の単一配列へ変更する
2. `src/server/database/atlas/schemas/song-persons.my.hcl` を追加し、既存 3 テーブル schema を削除する
3. `src/server/packages/Song/Domain/Models/*`、`SongIntegrityService.php`、`Application/*`、`Infrastructures/*` を新しい relation モデルへ差し替える
4. `src/server/packages/Support/Infrastructures/Mapper.php` を新契約に追従させる
5. songs テストを更新し、複数 role の登録・取得を検証する

## Decision Log

- 2026-05-04: role は song 専用 Enum としてまず導入し、人物一般の role 体系へは広げない。変更面積を song ドメイン内に閉じるため
- 2026-05-04: admin の楽曲編集 UI は role ごとに入力欄を分けつつ、保存契約は `personId + role + orderNo` の単一配列に統一する。運用導線と API モデルを分離して保つため

## Validation

- `mise run contract:compile:admin`
- `mise run api:generate`
- `mise run admin:generate`
- `mise run migrate:dry-run`
- `mise run migrate:testing`
- `mise run api:test -- tests/Feature/Api/Song`
- `mise run api:test -- tests/Integration/Song`
- `mise run api:test -- tests/Unit/Song`
