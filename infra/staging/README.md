# Staging Infrastructure

ステージング向けビルド定義の入口です。

## Cloud Build

- `cloudbuild/ci.yaml`: API / CLI / Admin / Viewer のコンテナを build / push し、migration job 実行後に Cloud Run へ deploy する
- `cloudbuild/api.yaml`, `cloudbuild/admin.yaml`, `cloudbuild/cli.yaml`, `cloudbuild/viewer.yaml`: 個別実行用の旧分割定義
- Cloud Build から実行する build command は YAML に直接定義する

## Dockerfiles

- `docker/api/Dockerfile`: Laravel API 用
- `docker/admin/Dockerfile`: Admin 用。build stage で `bun --filter admin build` し、runtime stage は Bun slim image を使う

## Notes

- `ci.yaml` は Artifact Registry の repository を `_ARTIFACT_REPOSITORY`、image 名を `_API_IMAGE` / `_CLI_IMAGE` / `_ADMIN_IMAGE` / `_VIEWER_IMAGE` で受け取る。Cloud Run の service / job 名は staging 用の値を直接定義する
- Admin / Viewer の URL 系 env は Cloud Build 上の `API_URL` / `PUBLIC_APP_URL` を使う
- Admin の `CACHE_URL` / `CACHE_TOKEN` は Cloud Run の runtime env / secret として渡す
- Cloud Build 上で使う tool version は各 YAML / Dockerfile に明示する
- Viewer の Cloudflare Workers 配置手順は、Workers 側の adapter / wrangler 設定を確定してから追加する
