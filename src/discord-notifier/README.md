# discord-notifier

Pub/Sub push を受け取り、Discord Webhook へ配達する Cloud Run 向けサービス (MoonBit)

## 責務

- `{env}-discord-notify` / `cloud-builds` からの Pub/Sub push を受ける
- アプリ通知 JSON (`action` / `status` / `embeds`) を配達する
- Cloud Build JSON を同じ契約に正規化して配達する
- `action` を env の routing で channel に引き当て、`DISCORD_WEBHOOK_<CHANNEL>` へ投稿する

## 契約

```json
{
  "action": "media.youtube_import",
  "status": "failed",
  "embeds": [
    {
      "title": "YouTube 取り込みでエラーが発生しました",
      "fields": [
        { "name": "executed_at", "value": "2026-08-11T22:00:00+09:00", "inline": true },
        { "name": "channel_id", "value": "UCxxxx", "inline": true }
      ]
    }
  ]
}
```

- `action`: 処理単位の識別子。Notifier が channel を決める
- `status`: `started` / `succeeded` / `failed` / `alert` など。embed に `color` が無いときの既定色
- `embeds`: Discord embed 配列 (必須・空不可)。見た目はここに寄せる

## 色

1. embed に `color` があればそれを使う
2. 無ければ `status` から既定色を付ける

## 振り分け

1. `DISCORD_ACTION_ROUTING` で `action` → channel を引く
2. マップに無い action は `DISCORD_DEFAULT_CHANNEL`(既定 `app`)
3. `DISCORD_WEBHOOK_<CHANNEL>` へ POST

新しい action を既存 channel へ送るだけなら routing の更新で足り、Notifier コードは触らない  
新しい channel を作るときは Webhook 用 env / secret が追加で必要

## ローカル

前提: MoonBit toolchain (`moon`)

```bash
cd src/discord-notifier
moon test
moon build --target native --release
```

起動例:

```bash
export PORT=8080
export DISCORD_DEFAULT_CHANNEL=app
export DISCORD_ACTION_ROUTING='{"cloud_build":"build"}'
export CLOUD_BUILD_TRIGGER_NAME=dev-isekai-ci
export DISCORD_WEBHOOK_APP='https://example.com/webhooks/...'
export DISCORD_WEBHOOK_BUILD='https://example.com/webhooks/...'
moon run cmd/main --target native
```

生 JSON の動作確認:

```bash
curl -sS -X POST "http://127.0.0.1:8080/" \
  -H 'Content-Type: application/json' \
  -d '{"action":"media.youtube_import","status":"failed","embeds":[{"title":"test error","fields":[{"name":"executed_at","value":"2026-01-01T00:00:00Z","inline":true}]}]}'
```

## 環境変数

| 名前 | 用途 |
|---|---|
| `PORT` | listen port (Cloud Run 既定) |
| `DISCORD_DEFAULT_CHANNEL` | マップに無い action の channel (既定 `app`) |
| `DISCORD_ACTION_ROUTING` | action -> channel のマップ (JSON) |
| `DISCORD_WEBHOOK_<CHANNEL>` | channel ごとの Webhook URL |
| `CLOUD_BUILD_TRIGGER_NAME` | 処理対象の Cloud Build trigger 名 |

## コンテナ

各環境の Dockerfile:

- `infra/development/docker/discord-notifier/Dockerfile`
- `infra/staging/docker/discord-notifier/Dockerfile`
- `infra/production/docker/discord-notifier/Dockerfile`

`mise.toml` の `moonbit_version` を build-arg `MOONBIT_VERSION` に渡す  
バージョンは `moonc` の文字列 (`0.10.6+80dc50f24` 形式) で固定する
