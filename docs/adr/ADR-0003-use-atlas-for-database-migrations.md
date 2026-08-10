---
id: ADR-0003
status: accepted
superseded_by: null
applies_to: [api, admin]
---

# マイグレーションツールに Atlas を採用する

## Context

Laravel は将来的に置き換える前提でいるため、できるだけ Laravel に依存しないシステムを構築したかった
Laravel 標準の Eloquent マイグレーションは Laravel と密結合しているため採用しなかった

Atlas は HCL による宣言的なスキーマ定義が魅力的で、フレームワーク非依存で使えるため採用した

## Decision

マイグレーションツールに Atlas を採用する

## Consequences

### Positive

- Laravel に依存しないマイグレーション管理ができる
- HCL による宣言的なスキーマ定義で可読性が高い
- 将来 Laravel を置き換えても、マイグレーション資産をそのまま持ち越せる

### Negative

- Eloquent との統合がないため、モデルとスキーマの同期は手動になる
