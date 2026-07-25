# Production Infrastructure

本番向けビルド定義の入口です。

## Cloud Build

- `cloudbuild/ci.yaml`: API / CLI / Admin / Viewer のコンテナを build / push し、Cloud Run service / job を deploy する
- Cloud Build から実行する build command は YAML に直接定義する

## Dockerfiles

- `docker/api/Dockerfile`: Laravel API 用。FrankenPHP を使う
- `docker/cli/Dockerfile`: migration job 用。PHP CLI と Atlas を含める
- `docker/admin/Dockerfile`: Admin 用。build stage で `bun --filter admin build` し、runtime stage は Bun slim image を使う
- `docker/viewer/Dockerfile`: Viewer deploy job 用。`viewer-deploy` を entrypoint にする

## Notes

- `ci.yaml` は Artifact Registry の repository を `_ARTIFACT_REPOSITORY`、image 名を `_API_IMAGE` / `_CLI_IMAGE` / `_ADMIN_IMAGE` / `_VIEWER_IMAGE` で受け取る
- Cloud Run の service / job 名は `prod-*` の値を YAML に直接定義する
- Admin の `PUBLIC_APP_URL` は Cloud Build substitution の `_PUBLIC_APP_URL` を build arg として渡す
- Viewer deploy job は `API_URL` / `SITE_URL` / Cloudflare Worker 名 / account ID / API token secret を受け取り、Cloudflare Workers へ deploy する
- Cloud Build 上で使う tool / base image の版は `mise.toml`（`[tools]` / `[vars]`）を Source of Truth とし、`tools/read-mise-value.sh` 経由で参照する
