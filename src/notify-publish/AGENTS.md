# notify-publish

MoonBit 製の通知 publish CLI

## 構成

- `payload.mbt`: アプリ通知 JSON の検証と Pub/Sub PublishRequest body 生成
- `config.mbt`: env から project / topic / metadata host を読む
- `pubsub.mbt`: metadata token 取得と Pub/Sub publish
- `publish.mbt`: stdin → 検証 → publish の一連処理

## コマンド

```bash
moon check
moon test
moon fmt
moon info
moon build --target native --release
```

最後に `moon info && moon fmt` を回す
