# Event 第1スライス ドメインメモ

## Summary

出来事 (`Event`) ドメインの第1スライス向けに、範囲・種別・日時・場所の判断を固定する
実装前の仕様メモであり、exec-plan や詳細 API 設計の前提になる

関連する将来構想は `docs/product-specs/20260503-song-media-admin-phase1/models.md` の出来事ドメインを参照する
本メモの判断がそちらと違う場合は、本メモを優先する

## Goals

- ヰ世界情緒が関わった出来事を広く `Event` として扱えるようにする
- 種別は「出来事の主目的」で分け、届け方は `Place` で表す
- 第1スライスは `Event` と `Place` (複数可) までにする

## Non-Goals

- `Performance` (楽曲披露関係) の実装
- 出演者 (`EventPerformer`) の実装
- `EventMediaLink` の実装
- Place 役割 (会場 / 配信先など) の細分
- 楽曲の「初出」定義の確定

## Terminology

| 層 | Event | Place |
|---|---|---|
| コード | `Event` | `Place` |
| 日本語 (正式) | 出来事 | 場所 |

補足:

- 画面見出しやナビも正式語に合わせ、主用語は「出来事」とする
- 「イベント」は日常語としてライブ寄りに聞こえやすいため、正式語にはしない
- Place の別名検討は保留し、当面は `Place` / 場所で進める
- 画面上の補助語として、`physical` を会場、`online` を配信先と出し分けるのは可とする

## Domain Decisions

### 1. Event の範囲

広く取る

ライブや配信に限らず、告知、投稿、展示、記事、コラボなども `Event` の対象にする
楽曲披露を持たない出来事も登録できる

### 2. EventType

初期値は次の 4 つとする

| 値 | 意味 | 例 |
|---|---|---|
| `live` | 公演としてのライブ | ワンマン、対バン、フェス、配信限定ライブ |
| `stream` | 配信枠・番組としての出来事 | 歌枠、記念配信、雑談配信、コラボ配信 |
| `music_release` | 楽曲 / MV / 音源などの公開時点 | MV 公開、配信リリース |
| `other` | 上記以外 | 告知、初ツイート、展示、記事 |

補足:

- 種別は出来事の主目的で決める
- 「歌ったかどうか」は種別の条件にしない
- 披露の事実は将来の `Performance` で持つ
- `stream` は「届け方が配信」という意味ではない
- 歌枠は `live` ではない (`stream`)
- 配信限定ライブは `live` である
- ワンマンの同時配信も `live` である (届け方は Place 側)

### 3. 日時

- `started_at` / `ended_at` の期間で持つ
- 日付しか分からない場合は時刻を `00:00:00` で埋める
- 瞬間的な出来事は `started_at == ended_at` とする

### 4. Place

- `Event` は複数の `Place` を持てる
- Place なしの `Event` も許容する
- 物理会場と online 配信先を同時に持てる
- Place の区分は `physical` / `online` とする
- 配信という提供方法は EventType ではなく、`online` な Place で表す

ケース例:

| ケース | 種別 | places |
|---|---|---|
| 会場ワンマン | `live` | `physical` |
| ワンマン + 同時配信 | `live` | `physical` + `online` |
| 配信限定ライブ | `live` | `online` のみ |
| 歌枠 | `stream` | `online` |
| 記念配信 | `stream` | `online` |

Place の最小属性案:

- `name` (必須)
- `kind` (`physical` / `online`)

URL は Place には持たない
物理会場に URL が付く違和感を避けるため、具体 URL は `Event` 側の任意属性として複数持てる
各 URL には任意の表示名 (`label`) を付けられる。未設定時は URL そのものを見せてよい
`online` な Place は「YouTube」などの名前を表し、配信 URL そのものは Event に載せる

## First Slice Scope

含める:

- `admin` での運用 (viewer の出来事一覧・詳細は後回し)
- `Event` CRUD に必要な最小属性
  - タイトル
  - 種別 (`EventType`)
  - `started_at` / `ended_at`
  - 説明
  - 表示有無
  - URL (任意・複数可。各 URL に表示名 `label` を任意で付けられる)
- `Place` マスタ (最小)
- `Event` と `Place` の複数関連

後回し:

- `viewer` の出来事 UI
- `Performance`
- 出演者
- Event 向け Media 関連
- Place 役割の細分
- 精度フラグ (日付のみ / 時刻ありの明示)
- `music_release` と既存 `Release` 集約の紐づけ

## Open Questions

- (なし。第1スライスの判断は揃った)

## Decision Log

- 2026-08-15: Event をイベント先行で進める。Performance は Event と Song を結ぶ関係として後続にする
- 2026-08-15: Event の範囲は広く取る
- 2026-08-15: EventType は `live` / `stream` / `music_release` / `other`
- 2026-08-15: `live` は公演としてのライブ。歌枠は含まない
- 2026-08-15: `stream` は配信枠・番組としての出来事。届け方そのものではない
- 2026-08-15: 配信という提供方法は Place(`online`) で表す
- 2026-08-15: 日時は from-to。日付のみは 00:00 埋め。瞬間は start == end
- 2026-08-15: Event は places を複数持てる。区分は `physical` / `online`
- 2026-08-15: コード名は `Event` / `Place` のままとする
- 2026-08-15: 日本語正式語は出来事 / 場所とする。見出しも出来事に合わせる
- 2026-08-15: Place の別名検討は保留する
- 2026-08-15: 第1スライスの配信面は admin のみとする。viewer は後回し
- 2026-08-15: URL は Place ではなく Event の任意属性とする。複数可
- 2026-08-16: Event URL には任意の表示名 (`label`) を持てる。生 URL の羅列を避けるため
- 2026-08-15: `music_release` と `Release` の紐づけは第1スライスでは作らない
- 2026-08-15: Place なしの Event を許容する
