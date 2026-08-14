# notify-publish

通知 JSON を Pub/Sub topic へ publish する MoonBit native CLI

Discord 配達はしない。発信側から `{env}-discord-notify` へ載せる入口

## 責務

- stdin のアプリ通知 JSON (`action` / `status` / `content?` / `embeds?`) を検証する
- GCE metadata から default SA の access token を取る
- `NOTIFICATION_TOPIC` へ `topics:publish` する

## 契約

`discord-notifier` と同じアプリ通知 JSON

```json
{
  "action": "viewer.deploy",
  "status": "succeeded",
  "embeds": [
    {
      "title": "Viewer deploy succeeded",
      "fields": [
        { "name": "site_url", "value": "https://example.com", "inline": true }
      ]
    }
  ]
}
```

## 使い方

```bash
export GOOGLE_CLOUD_PROJECT=my-project
export NOTIFICATION_TOPIC=dev-discord-notify

notify-publish <<'EOF'
{"action":"viewer.deploy","status":"succeeded","content":"ok"}
EOF
```

ローカルビルド:

```bash
cd src/notify-publish
moon test
moon build --target native --release
```

## 環境変数

| 名前 | 用途 |
|---|---|
| `GOOGLE_CLOUD_PROJECT` | GCP project id |
| `NOTIFICATION_TOPIC` | publish 先 topic 名 |
| `GCE_METADATA_HOST` | metadata host (任意。Cloud が付ける値を使う) |
