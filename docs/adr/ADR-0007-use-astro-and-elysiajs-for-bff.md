---
id: ADR-0007
status: accepted
superseded_by: null
applies_to: [admin, client]
---

# BFF の実装に Astro と Elysia.js を採用する

## Context

管理画面の認証処理をシンプルにするために BFF を導入した。
また、複数のデータ取得を1エンドポイントに集約できる点も評価しており、その用途での活用も検討中である。

BFF は Astro の API 機能と Elysia.js を組み合わせて実現している。
Elysia.js は Bun 上でのパフォーマンスの良さと、Eden による型安全なクライアント生成が決め手になった。

現在は管理画面のみ BFF を持つが、将来的にはユーザー向けクライアントにも導入を検討している。

## Decision

BFF を Astro の API 機能と Elysia.js で実装する。

## Consequences

### Positive

- 認証処理をクライアントから切り離してシンプルにできる
- Eden により BFF とクライアント間で型安全な通信ができる
- Bun 上での高いパフォーマンスが得られる

### Negative

- Elysia.js・Eden はエコシステムがまだ小さく、情報量が少ない
- BFF が増えることでシステムの構成が複雑になる
