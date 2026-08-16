# Title

Event 第1スライス (admin: Event + Place)

## Status

in-progress

## Background

`docs/product-specs/20260815-event-first-slice/README.md` で、出来事 (`Event`) と場所 (`Place`) の第1スライス判断が固まった
現状は楽曲・リリース・メディアまでは admin で運用できるが、ヰ世界情緒の活動時系列を載せる `Event` / `Place` は未実装である

第1スライスでは viewer や Performance には入らず、admin で出来事と場所を登録・編集できる状態までを目指す
変更面が広いため、Release / Person と同様に foundation → Place admin → Event admin に分割して進める

## Goal

admin で `Place` マスタと `Event` (複数 Place・複数 URL 可、Place なし可) を CRUD できる状態にする
日本語 UI の主用語は「出来事」「場所」とする

## Scope

- 仕様メモ: `docs/product-specs/20260815-event-first-slice/README.md` を正とする
- DB: `places` / `events` / `event_places` / `event_urls` と Atlas 登録
- server: `Place` / `Event` パッケージ (Domain → Application → Infrastructures → Route)、Eloquent、権限、監査対象
- contracts: admin 向け `places` / `events` TypeSpec と生成物
- admin: BFF、場所画面、出来事画面、サイドナビ

## Non-Scope

- `viewer` の出来事・場所 UI
- `Performance` / 出演者 / `EventMediaLink`
- Place 役割の細分、Place への URL
- `music_release` と `Release` 集約の紐づけ
- 日時精度フラグ (日付のみ明示)

## Split Plans

土台ブランチは `feature/event-first-slice`

1. `event-foundation`: DB・pure domain・autoload のみ (runtime / contracts / admin は含めない。Eloquent は ADR-0011 により対象外)
2. `place-admin`: Place の contracts・API・admin CRUD
3. `event-admin`: Event の contracts・API・admin CRUD (Place 関連付けと複数 URL を含む)

各分割は着手時に `docs/exec-plans/active/YYYYMMDD-<slug>.md` へ詳細 Steps を切り出してよい
本ファイルは第1スライス全体の傘計画として維持する

## Acceptance Criteria

- admin で場所を `name` / `kind` (`physical` / `online`) 付きで登録・検索・更新・削除できる
- admin で出来事をタイトル・種別・期間・説明・表示有無・複数 URL・複数 Place (0 件可) 付きで登録・検索・取得・更新・削除できる
- EventType は `live` / `stream` / `music_release` / `other` である
- 日時は `started_at` / `ended_at` で持ち、日付のみは 00:00 埋め、瞬間は start == end で表現できる
- URL は Event 側のみで、Place には URL を持たない
- viewer・Performance・Release 紐づけは含まれない

## Steps

- [x] 1. `event-foundation`: `places` / `events` / `event_places` / `event_urls` の Atlas スキーマを追加し `atlas.hcl` に登録する。カラムは仕様メモの最小構成に閉じる (`events`: title, type, started_at, ended_at, description, is_display, timestamps。`places`: name, kind, timestamps。`event_places`: event_id, place_id。`event_urls`: event_id, url, order_no, label (任意))
- [x] 2. `event-foundation`: `packages/Place` / `packages/Event` の Domain Models と `composer.json` の PSR-4 を追加する。Application / HTTP / contracts / admin は触らない (Eloquent は ADR-0011 により対象外)
- [ ] 3. `place-admin`: admin contracts に Place CRUD / search を追加し、生成物を更新する。`PermissionValue` / `AuditTargetType` に Place を足す
- [ ] 4. `place-admin`: Place の server UseCase / Repository / HTTP / テストを実装する
- [ ] 5. `place-admin`: admin BFF と場所一覧・作成・詳細/編集画面、ナビを追加する
- [ ] 6. `event-admin`: admin contracts に Event CRUD / search を追加し、生成物を更新する。request に places・urls を含め、`PermissionValue` / `AuditTargetType` に Event を足す
- [ ] 7. `event-admin`: Event の server UseCase / Repository / HTTP / テストを実装する。Place 0 件と URL 複数、期間・種別の保存をカバーする
- [ ] 8. `event-admin`: admin BFF と出来事一覧・作成・詳細/編集画面、ナビを追加する。見出しは「出来事」、Place の `physical` / `online` は会場 / 配信先として出し分けてよい
- [ ] 9. 差分が Non-Scope に踏み込んでいないことを確認し、`code-reviewer` でレビューして指摘を潰す

## Decision Log

- 2026-08-15: 仕様の正は `docs/product-specs/20260815-event-first-slice/README.md` とする
- 2026-08-15: 第1スライスは admin のみ。viewer は後回し
- 2026-08-15: EventType は `live` / `stream` / `music_release` / `other`
- 2026-08-15: `live` は公演。歌枠は `stream`。配信限定ライブは `live`
- 2026-08-15: 配信という提供方法は Place(`online`) で表し、EventType にはしない
- 2026-08-15: 日時は from-to。日付のみは 00:00 埋め。瞬間は start == end
- 2026-08-15: Event は places を複数持てる。Place なしも許容する
- 2026-08-15: URL は Event の任意・複数。Place には持たない
- 2026-08-16: Event URL の `label` (表示名) は任意。未設定時は URL そのものを見せてよい
- 2026-08-15: コード名は `Event` / `Place`、日本語正式語は出来事 / 場所
- 2026-08-15: `music_release` と `Release` の紐づけは作らない
- 2026-08-15: 実装は foundation → Place admin → Event admin に分割する。Place を先に運用可能にし、Event 作成時の関連付けを単純にするため
- 2026-08-15: `Place` と `Event` は別パッケージにする。Person / Song と同様にマスタと利用側を分けるため
- 2026-08-15: foundation では Eloquent を追加しない。ADR-0011 により永続化は後続の Repository (emonkak) で行う
- 2026-08-15: `places.name` は unique とする。マスタとしての再利用を優先するため
- 2026-08-15: `Event` 集約は `EventPlaceLinks` と `EventUrls` を保持する。永続化テーブルは `event_places` / `event_urls`

## Validation

- `mise run migrate:dry-run` (または同等) でスキーマが通ること
- Place / Event の Feature テストが通ること
- admin で場所なし出来事、物理+online の出来事、URL 複数の出来事を登録・更新できること
- `git diff` で viewer / Performance / Release 紐づけが含まれないこと
