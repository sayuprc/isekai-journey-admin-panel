# Production Infrastructure

本番向けビルド定義の入口です。

## Cloud Build

- `cloudbuild/api.yaml`: API のコンテナを build / push し、Cloud Run へ deploy する
- `cloudbuild/admin.yaml`: Admin のコンテナを build / push し、Cloud Run へ deploy する
- `cloudbuild/viewer.yaml`: Viewer を Cloud Build 上で build する
- Cloud Build から実行する build command は各 YAML に直接定義する

## Dockerfiles

- `docker/api/Dockerfile`: Laravel API 用
- `docker/admin/Dockerfile`: Admin 用。build stage で `bun --filter admin build` し、runtime stage は Bun slim image を使う

## Notes

- `api.yaml` と `admin.yaml` は Artifact Registry の repository を `_ARTIFACT_REPOSITORY`、Cloud Run の service 名を `_SERVICE` で受け取る
- Admin / Viewer の URL 系 env は Cloud Build 上の `API_URL` / `PUBLIC_APP_URL` を使う
- Admin の `CACHE_URL` / `CACHE_TOKEN` は Cloud Run の runtime env / secret として渡す
- Cloud Build 上で使う tool version は各 YAML / Dockerfile に明示する
- Viewer の Cloudflare Workers 配置手順は、Workers 側の adapter / wrangler 設定を確定してから追加する
