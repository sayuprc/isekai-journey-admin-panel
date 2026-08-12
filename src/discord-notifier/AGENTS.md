# discord-notifier

MoonBit 製の Discord 通知配達サービス

## 構成

- `parse.mbt`: Pub/Sub envelope / アプリ通知 / Cloud Build 正規化
- `config.mbt`: env からの action -> channel 振り分け
- `dedup.mbt`: cloud_build started のプロセスローカル重複抑止
- `discord.mbt`: embeds に status 既定色を付けて Webhook POST
- `handle.mbt` / `server.mbt`: HTTP 配線

## コマンド

```bash
moon check
moon test
moon fmt
moon info
moon build --target native --release
```

最後に `moon info && moon fmt` を回す
