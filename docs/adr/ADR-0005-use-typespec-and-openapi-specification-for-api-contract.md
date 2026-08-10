---
id: ADR-0005
status: accepted
superseded_by: null
applies_to: [api, admin, client]
---

# API コントラクトの定義に TypeSpec と OpenAPI Specification を採用する

## Context

クライアントとのコントラクト管理およびコード生成のために API 仕様を定義する必要があった
API 仕様の記述フォーマットとして OpenAPI Specification を採用した

OpenAPI の YAML を手動で書くのは煩雑なため、TypeSpec を使って OpenAPI Spec を生成する方針にした

## Decision

API コントラクトの定義に TypeSpec を採用し、OpenAPI Specification を生成する

## Consequences

### Positive

- YAML を手書きせずに型安全な形で API 仕様を定義できる
- 生成した OpenAPI Spec からクライアントコードを自動生成できる
- コントラクトファーストな開発ができる

### Negative

- TypeSpec 自体の学習コストがある
- TypeSpec のエコシステムはまだ成熟途上であり、情報量が少ない
