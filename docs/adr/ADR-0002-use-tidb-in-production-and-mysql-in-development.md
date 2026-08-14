---
id: ADR-0002
status: accepted
superseded_by: null
applies_to: [api, admin]
---

# 本番 DB に TiDB、開発環境に MySQL を採用する

## Context

個人開発のため、DB はコストを抑えられる SaaS を選定した
PostgreSQL を第一候補としたが、無料枠で要件を満たすサービスが見つからなかった

TiDB は無料枠の容量が他 SaaS より大きく、業務経験もあったため本番 DB として採用した

ローカルで TiDB を起動するとパフォーマンスが悪いため、開発環境では MySQL を使用している
TiDB は MySQL 互換であり、TiDB 固有の機能を使う予定がないため、この構成でも問題ない

将来的にはローカルも TiDB に統一したいが、起動速度の問題が解消されるまでは現状を維持する

## Decision

本番 DB に TiDB を採用する。開発環境では MySQL を使用する

## Consequences

### Positive

- 無料枠で十分な容量を確保できる
- 業務経験があるメンバーがすぐに扱える
- MySQL 互換のため開発・本番で同じクエリが動く

### Negative

- 本番と開発で厳密には異なる DB を使うことになる
- TiDB 固有の挙動に依存するコードを書いた場合、開発環境で検出できないリスクがある
