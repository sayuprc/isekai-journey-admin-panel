---
id: ADR-0004
status: accepted
superseded_by: null
applies_to: [admin, viewer]
---

# フロントエンドに Astro と SolidJS を採用する

## Context

フロントエンドは管理画面・ユーザー向けクライアントともに同じ技術スタックを採用した

フルスタックフレームワークが提供する機能(SSR、API ルートなど)は必要なかったため、Astro を選んだ
Astro の Islands Architecture によるコンポーネント単位のインタラクティビティという独特なアーキテクチャが魅力的だった

インタラクティブなコンポーネントには UI フレームワークが必要なため、SolidJS を採用した
SolidJS は仮想 DOM を使わない宣言的 UI を実現しており、React と比べて簡素に書ける点が決め手になった

## Decision

フロントエンドに Astro + SolidJS を採用する

## Consequences

### Positive

- 必要な箇所だけインタラクティブにできるため、シンプルな構成を保てる
- SolidJS により仮想 DOM のオーバーヘッドなく宣言的 UI が書ける
- 管理画面・ユーザー向けクライアントで技術スタックを統一できる

### Negative

- Astro・SolidJS ともにエコシステムが React と比べて小さく、情報量が少ない
