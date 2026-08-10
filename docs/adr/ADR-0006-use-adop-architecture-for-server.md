---
id: ADR-0006
status: accepted
superseded_by: null
applies_to: [api, admin]
---

# サーバーに ADOP アーキテクチャを採用する

## Context

長期的に保守しやすいコードにするため、アーキテクチャのガイドラインが必要だった

クリーンアーキテクチャや Hexagonal Architecture は原則が抽象的で、「どこにコードを置くか」の解釈が人によってばらつく問題がある

ADOP (Application Domain Others Pattern) は Hexagonal Architecture を実践するための具体的なガイドラインを提供するアーキテクチャで、ルールが2つだけとシンプルである
Application・Domain を厳密に設計し、その他はすべて Others として扱うことで、開発コストと品質のバランスを取れると判断した

参考: https://nrslib.com/adop

## Decision

サーバーに ADOP アーキテクチャを採用する

## Consequences

### Positive

- 「このコードはどの層に置くか」の迷いが減る
- Application・Domain にビジネスロジックが集中するため品質を保ちやすい
- フレームワーク(Laravel)への依存を Application・Domain から切り離せる
- ルールが少ないため、厳密なクリーンアーキテクチャより習得しやすい

### Negative

- ADOP は一般的な用語ではなく、外部の情報が少ない
- Others 層の設計は各自の判断に委ねられるため、ばらつきが生じる可能性がある
