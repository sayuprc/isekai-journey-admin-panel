# Agent Map

エージェント向けの共通入口です。まずこの文書を見てください

## まず見る文書

| 文書 | 内容 |
|---|---|
| `README.md` | セットアップと主要コマンド |
| `ARCHITECTURE.md` | Source of Truth と変更ルート |
| `FRONTEND.md` | 管理画面と閲覧サイトの UI 方針 |
| `PLANS.md` | 実行計画を書くタイミング |
| `docs/INDEX.md` | 文書の置き場所と一覧 |

## 触り始め

| 変更したいもの | 最初に触る場所 |
|---|---|
| API の request / response / version | `src/contracts` |
| サーバー側の業務ロジック | `src/server` |
| 管理画面 UI / BFF | `src/admin` |
| 閲覧サイト UI | `src/viewer` |
| Discord 通知配達 | `src/discord-notifier` |
| 通知 Pub/Sub publish | `src/notify-publish` |
| アプリ通知 JSON 契約 | `src/notify-contract` |
| ローカル / 環境別インフラ | `infra/README.md` |
| それ以外 | `ARCHITECTURE.md` |

## よく辿る下位文書

| 文書 | 内容 |
|---|---|
| `docs/design-docs/INDEX.md` | 設計原則 |
| `docs/exec-plans/README.md` | 実行計画の詳細ルール |
| `docs/operations/INDEX.md` | 運用手順 |
| `docs/tech-debt-tracker.md` | 技術的負債 |
| `docs/adr/INDEX.md` | ADR |

## 文書の置き場所

- `docs/INDEX.md`

## 参照メモ

| 文書 | 内容 |
|---|---|
| `docs/references/harness-engineering-notes.md` | 背景メモ |
| `docs/references/agent-instruction-placement.md` | Agent 指示の置き場所 |

共通ルールをこの文書に増やしすぎず、詳細は下位文書へ分けます
