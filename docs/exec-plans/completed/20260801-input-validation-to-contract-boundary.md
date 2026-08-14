# 入力形式検証を契約境界へ集約し Field/Fields を廃止する

## Title

ADR-0014: 入力形式検証の契約境界への集約 (エラーハンドリング Stage 3)

## Status

completed

## Background

ADR-0013 で業務エラーを例外へ移行した際、422 の全 field 集約はドメイン側の
`Field` / `Fields` (Support\Domain\Validation) が担う設計とした。しかし運用してみると

- 例外 (fail-fast) の上に集約 (fail-slow) を再建するためのアダプタとして
  `Field` の器具立てが必要になり、UseCase / IntegrityService の可読性を損ねる
- ドメインサービスがワイヤ形式の field 名 ('publishedAt' 等) を知る層の越境が起きる
- 形式ルールは TypeSpec 契約 (@minLength / @format / uuid scalar) と VO で既に二重管理
- OpenApiValidator は field 名つき 422 を生成済みだが League validator の fail-fast
  により 1 field しか報告できない (実装内 TODO として残存)

VO 固有の形式ルールは実質 TrackTitle / MediumName の非空 (API 経路では正規化後のため
発火しない) と YouTubeChannelId (CLI 専用) のみで、移行の表面積は小さいことを確認済み

## Goal

入力形式検証の単一情報源を TypeSpec 契約とし、422 の生成を OpenApiValidator に一元化する
ドメインの `InvalidDomainException` は「表明違反 = 500 = バグ」に純化し、
`Field` / `Fields` / `DomainValidationException` を全廃する

## Scope

- docs/adr/ADR-0014 新規作成、ADR-0013 の部分 supersede 注記
- src/contracts (形式制約の補完) と生成物
- src/server/app/Http/Middleware/OpenApiValidator.php (収集型 body 検証の組み込み)
- src/server/app/Http/Responses/ApiExceptionRenderer.php (validationFailed arm の削除)
- src/server/packages 全体の Field / Fields / DomainValidationException 利用箇所
- src/server/app/Console (CLI 4 コマンドの例外表示確認)
- src/admin の 422 details 消費箇所
- .claude/rules/01-backend.md、code-reviewer チェックリスト
- 関連テスト (Feature / Integration / Unit)

## Non-Scope

- BusinessRuleViolationException / 認証・認可・NotFound 例外の設計 (現行維持)
- viewer 側 (公開 API のエラー消費が変わらない限り触らない)
- Result 型の再導入や例外方針そのものの再検討 (ADR-0013 の骨格は維持)

## Acceptance Criteria

- 不正な body を含むリクエストで 422 が返り、details に**全**違反 field が
  パス形式キー (例: `media/0/tracks/1/trackNo`) + 日本語メッセージで載る
- `grep -r 'Field\b\|Fields::\|DomainValidationException' src/server/packages` が 0 件
- UseCase / IntegrityService の VO 構築が直接 `new` のみになっている
- ドメイン検証と契約検証の不整合時 (契約をすり抜けた不正値) は 500 internal_error になる
- CLI コマンドに不正引数を渡すとエラーメッセージ表示 + 非ゼロ exit する
- `mise run api:ecs` / `api:phpstan` / `api:arkitect` / `api:test` と admin の検証が全て通る
- ADR-0014 が accepted で存在し、ADR-0013 に部分 supersede の注記がある

## Steps

- [x] 1. プロトタイプ: 収集型 JSON Schema validator (opis/json-schema 第一候補) が
      TypeSpec 生成の OpenAPI yaml スキーマを解決でき、ネスト body (releases update) で
      全違反 + JSON pointer を収集できることを検証する。不可なら代替 (league の
      schema 部分の fork / justinrainbow 等) を比較して Decision Log に記録
- [x] 2. ADR-0014 を作成し、ADR-0013 の Decision L36-38 に superseded 注記を入れる
- [x] 3. 契約の形式制約を VO ルールと突き合わせて補完する (tracks.title / media.name の
      minLength(1) 等)。契約と生成物を再生成してコミット
      → 突き合わせの結果、trackTitle / mediumName / mediaTitle の @minLength(1)、
      mediaUrl @format("uri")、mediaPublishedAt utcDateTime、uuid / orderNo scalar まで
      契約に既に存在し補完不要だった。契約変更なし
- [x] 4. OpenApiValidator に body の収集検証を実装する: League が invalid と判定した
      リクエストに対して body をスキーマへ全件照合し、違反があれば keyword → 日本語
      メッセージ変換の上 `ApiError::validationFailed` で返す。既存 TODO を解消
      (App\Http\OpenApi\BodyErrorCollector / BodyErrorFormatter / SchemaErrorMessages
      正常系はコスト増ゼロ、通過判定は League のまま)
- [x] 5. server の Field / Fields 全 16 箇所を直接 `new` に置換し、
      Support\Domain\Validation と DomainValidationException、Renderer の 422 arm を削除
      Tracks / Media の順序重複チェックは BusinessRuleViolationException へ移す
- [x] 6. CLI 4 コマンド (IssueRegistrationToken / YouTubeChannel 3 種) の Field を除去し、
      InvalidDomainException がコマンドエラー表示 + 非ゼロ exit になることを確認
- [x] 7. admin の 422 details 消費箇所を、パス形式キーに追従させる
      → flat な field 名は互換。getFieldError を prefix 一致に拡張しネストパスを配下 field として表示
- [x] 8. テストを移行する: Feature の 422 期待値 (details キー / メッセージ)、
      Fields 前提の Unit / Integration の書き換え
- [x] 9. .claude/rules/01-backend.md の検証規約 (Field 使い分け節を含む) を新原則で
      書き直し、code-reviewer チェックリストを追従
- [x] 10. 全検証 (server + admin) を通し、code-reviewer レビュー → 修正 → PR 作成

## Decision Log

- 2026-08-01: 形式検証の単一情報源を VO から TypeSpec 契約へ移す。契約と VO の
  二重管理が既に存在しており、契約ファーストのリポジトリでは契約が正であるべきため
- 2026-08-01: 422 の全 field 集約は収集型 JSON Schema validator を body 検証に併用して
  実現する。League は routing / security / response 検証で続投。エッジの fail-fast TODO も
  これで解消する
- 2026-08-01: ドメインの InvalidDomainException は表明違反 (500 = バグ) に純化し、
  Field / Fields / DomainValidationException は全廃する
- 2026-08-01: CLI は集約 UX を持たず、例外メッセージのコマンドエラー表示で足りるとする
- 2026-08-01: ADR は新規 0014 とし、0013 を部分 supersede する (経緯を履歴として残す)
- 2026-08-01: 検証エラーメッセージは validator の英語文言をそのまま出さず、
  keyword (required / minLength / format 等) → 日本語の変換表を middleware (app 層) に持つ
- 2026-08-01: 配列内の順序重複 (trackNo / position) は JSON Schema で表現できないため
  BusinessRuleViolationException (400) へ移す。422 → 400 に変わる点は admin 側の
  ハンドリングと合わせて確認する
- 2026-08-01: 収集型 validator は opis/json-schema ^2.6 に確定 (プロトタイプで成立を確認)
  既知の制限として、同一スキーマの required 違反は同階層 properties の検証を短絡させる
  (欠落 field があるとその報告のみになる)。現行の常に 1 件より改善のため許容する
  anyOf (nullable) の違反は複数メッセージになるため formatter 側で平滑化する

## Validation

- PR #913 をレビュー・マージ済み (2026-08-01、refactor/api-handling へ)
- CLI (2026-08-01): media:youtube-channel:remove に不正 ID を渡し、
  「YouTube チャンネルIDの形式が不正です」表示 + exit 1 を確認
- server (2026-08-01): api:ecs / api:phpstan / api:arkitect / api:test (603 件) 全通過
- code-reviewer レビュー (2026-08-01): 重大 1 件 (opis の pointer 未解決が素の RuntimeException で
  catch を素通りし 422 が 500 化) を catch 拡張 + 設定バグの LogicException 分離で修正、
  再現ケースで解消を確認。警告 1 件 (チェックリストの旧記述残りと 3 箇所同期漏れ) を同期修正
- admin (2026-08-01): admin:check (biome / eslint / stylelint) 通過
- Step 1 プロトタイプ (2026-08-01): opis/json-schema ^2.6 で成立を確認
  OpenAPI 3.1 yaml を registerRaw + pointer fragment $ref で無変換解決、
  setMaxErrors + ErrorFormatter でネスト含む全違反を JSON pointer キーで収集、
  format (date / uuid) はデフォルト検証。parse 262ms / validate 35ms
