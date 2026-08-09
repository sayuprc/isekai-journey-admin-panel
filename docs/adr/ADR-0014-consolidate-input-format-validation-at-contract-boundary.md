---
id: ADR-0014
status: accepted
superseded_by: null
applies_to: [api, admin]
---

# 入力形式検証を契約境界へ集約する

## Context

ADR-0013 で業務エラーを例外へ移行した際、422 の全 field 集約は
`Support\Domain\Validation` の `Field` / `Fields` が担う設計とした
運用の結果、次の歪みが確認された

- 例外は fail-fast (最初の失敗で短絡) だが、フォーム検証は fail-slow
  (全 field を集めて報告) を要求する。`Field` はこの不整合の上に集約を
  再建するアダプタであり、UseCase / IntegrityService に器具立てが漏れる
- ドメインサービスがワイヤ形式の field 名 ('publishedAt' 等) を知っており、
  契約 (プレゼンテーション) の語彙が Domain 層へ越境している
- 入力形式ルールは TypeSpec 契約 (@minLength / @format / uuid scalar) と
  ValueObject の isValid で既に二重管理になっており、
  「ValueObject を検証の単一情報源とする」原則は形式ルールの層では既に破れていた
- `OpenApiValidator` middleware は field 名つき 422 を生成済みだが、
  League validator の fail-fast により 1 field しか報告できない

## Decision

入力形式検証 (必須 / 型 / 長さ / format / enum) の単一情報源を TypeSpec 契約とし、
422 の生成を `OpenApiValidator` middleware に一元化する

- request body は opis/json-schema による収集検証を併用し、全違反を
  JSON pointer 由来の field パス (例: `media/0/tracks/1/trackNo`) で一括報告する
  ルーティング / セキュリティ / レスポンス検証は League validator を続投する
- 検証エラーメッセージは keyword → 日本語の変換表を app 層 (middleware) に持つ
- ドメインの `InvalidDomainException` は「表明違反 = 500 = バグ」に純化する
  `new` による ValueObject 構築は常に「この値は正しいはず」の宣言であり、
  破れたら契約とドメインの不整合として大きな音で表面化させる
- `Field` / `Fields` / `DomainValidationException` は廃止する
  UseCase / IntegrityService は ValueObject を直接 `new` するだけになる
- 契約 (JSON Schema) で表現できない配列内ルール (トラック順序の重複等) は
  `BusinessRuleViolationException` で表現する
- 契約境界を通らない CLI は、例外メッセージのコマンドエラー表示で足りるとし
  集約 UX を持たない

本 ADR は ADR-0013 の Decision のうち「入力形式の検証は Application 層の組立て役が担い、
ValueObject を検証の単一情報源としたまま全 field のエラーを集約する」の部分を置き換える
例外方針そのもの (Result 撤去、例外 → HTTP 変換の一元化、統一エンベロープ) は維持する

## Consequences

### Positive

- UseCase / IntegrityService から検証の器具立てが消え、VO 構築は `new` を並べるだけになる
- 形式ルールの二重管理が解消し、契約が唯一の真実になる。フロントは同じ契約から
  クライアント側検証も導出できる
- 422 の field パスがネスト構造まで正確になり、既存の「項目ごとのエラーを表示したい」
  TODO が解消する
- ワイヤ形式の語彙が Domain 層から消え、層の責務が明確になる

### Negative

- 契約に形式制約を書き切る規律が必要になる。契約の漏れはドメインの表明違反 (500)
  として表面化する
- 同一スキーマ内の required 違反は同階層 properties の検証を短絡させるため、
  field 欠落と形式違反が同時にあると欠落の報告が先行する (opis の仕様)
- 配列内の順序重複が 422 から 400 (business_rule_violation) に変わる
