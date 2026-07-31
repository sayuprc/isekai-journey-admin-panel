---
id: ADR-0013
status: accepted
superseded_by: null
applies_to: [api, admin, client]
---

# 業務エラーの表現を Result から例外へ移行する

## Context

ADOP アーキテクチャ ([ADR-0006](ADR-0006-use-adop-architecture-for-server.md)) の下、
期待される業務エラーは `sayuprc/result-type` の `Ok` / `Err` で表現し、
Domain → UseCase → Presenter と戻り値で伝搬させていた

実際に運用すると、層をまたぐたびに `mapErr` / `andThen` による詰め替えが必要で、
Result の伝搬は server の 131 ファイルに波及していた。
煩雑さの割に得られる利益は薄く、次の問題も抱えていた

- ValueObject はコンストラクタで既に `InvalidDomainException` を throw しており、
  Result を返す `create()` との二重 API になっていた
- `andThen` の短絡により、422 のバリデーションエラーは最初の 1 field しか報告されない
- エラー → HTTP 変換の知識が 48 個の Presenter に分散していた (`ResolvesUseCaseError` trait)

## Decision

期待される業務エラーも例外で表現し、Result 型を server の全層から撤去する

- 例外 → HTTP 変換は Laravel の例外ハンドラに集約する。
  例外クラス → {code, status} の対応表は app 層に 1 枚だけ持ち、packages は HTTP も code も知らない
- ドメイン層の例外もハンドラが直接レンダリングし、UseCase 境界での詰め替えはしない
- エラーレスポンスは全ステータスで `{code, message, details?}` の統一エンベロープに再設計する。
  code はカテゴリ単位の enum (`unauthenticated` / `permission_denied` / `not_found` /
  `validation_failed` / `business_rule_violation` / `internal_error`) で例外クラスと 1:1 とする
- HTTP ステータスの割当 (401/403/404/422/400) は現状維持とする
- 入力形式の検証は Application 層の組立て役が担い、ValueObject を検証の単一情報源としたまま
  全 field のエラーを集約して 1 つの例外を投げる
- ValueObject の構築は public コンストラクタに一本化し、`create()` / `reconstruct()` を廃止する
- 移行はワイヤ形式の刷新を先行させ、その後パッケージ単位で内部を例外化する 2 段階で行う

## Consequences

### Positive

- 正常系の UseCase は OutputData を直接返し、Presenter は成功系の変換だけになる
- エラー変換の実装が 1 箇所に集約され、分類の追加・変更が局所化する
- フロントは HTTP ステータスに加えて機械可読な code で分岐できる
- 422 が全 field のエラーを一括報告できるようになる
- ValueObject の構築経路が 1 つになり二重 API が解消する

### Negative

- エラーの可能性が型シグネチャに現れなくなる (PHP の throws は強制されないため PHPDoc とレビューで補う)
- ワイヤ形式の変更に伴い admin / viewer のエラー消費コードとテストの更新が必要になる
- catch 漏れの例外は 500 として表面化する
