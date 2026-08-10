---
id: ADR-0009
status: accepted
superseded_by: null
applies_to: [api, admin, client]
---

# 開発環境に Docker を採用する

## Context

バックエンドには PHP/Laravel を採用している(ADR-0001)。PHP の実行環境をホストマシンに直接セットアップすると、メンバー間でのバージョン差異や OS 差異によって環境再現が困難になる
また、DB(MySQL/TiDB)などの依存サービスも含めて開発環境を統一する必要があった

一方、フロントエンド(Bun)は mise によるランタイムバージョン管理が容易なため、Docker を使わずホストマシン上で直接実行する

Docker を使うことで、PHP や依存サービスの実行環境をコンテナとして定義し、全メンバーが同一環境で開発できるようにする

## Decision

PHP/Laravel の実行環境および DB などの依存サービスを Docker(docker compose)で構築する
`docker compose up` で必要なサービスをまとめて起動できるようにする

フロントエンドは Bun のバージョン管理を mise に任せ、ホストマシン上で直接実行する

## Consequences

### Positive

- ホストマシンの OS・ランタイムバージョンに依存せず、PHP 環境を統一できる
- `docker compose up` だけで依存サービスを含む開発環境全体を起動できる
- 本番環境との差異を最小化しやすい

### Negative

- Docker の知識が必要になる
- コンテナのビルドやファイルシステムの同期によって、ネイティブ実行と比べてパフォーマンスが低下することがある
