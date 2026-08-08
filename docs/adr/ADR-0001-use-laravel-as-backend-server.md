---
id: ADR-0001
status: accepted
superseded_by: null
applies_to: [api, admin]
---

# バックエンドの実装に PHP/Laravel を採用する

## Context

当初、API は Go または Rust を用いた gRPC サーバーとして構築する計画だった。
しかし学習コストが想定より高く、開発が長期間停滞した。

この状況を打開するため、経験のある PHP/Laravel で実装する方針に切り替えた。
また、リポジトリを分けることの煩雑さも考慮し、管理画面サーバーと API を同一の Laravel アプリケーションに統合している。

将来的には TypeScript または Go へ移行することを想定しており、そのタイミングで本 ADR は superseded になる予定。

## Decision

バックエンド全体を PHP/Laravel で実装する。管理画面サーバーと API は同一アプリケーションとして統合する。

## Consequences

### Positive

- 開発を前に進められるようになった
- PHP に慣れたメンバーがすぐに着手できる

### Negative

- 将来 TypeScript または Go へ移行する際、この ADR は superseded になる
- 統合構成のため、将来の分離にはある程度のリファクタリングが必要になる
