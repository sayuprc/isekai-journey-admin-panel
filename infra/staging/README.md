# Staging Infrastructure

ステージング向けビルド定義の入口です。

## Cloud Build

- `cloudbuild/ci.yaml`: API / CLI / DB migrate / Admin / Viewer のコンテナを build / push し、Cloud Run service / job を deploy する
- Cloud Build から実行する build command は YAML に直接定義する

## Dockerfiles

- `docker/api/Dockerfile`: Laravel API 用。FrankenPHP Alpine を使う
- `docker/cli/Dockerfile`: artisan job 用。PHP CLI と Laravel application を含める
- `docker/db-migrate/Dockerfile`: migration job 用。Atlas と schema 定義のみを含める
- `docker/admin/Dockerfile`: Admin 用。build stage で `bun --filter admin build` し、runtime stage は Bun slim image を使う
- `docker/viewer/Dockerfile`: Viewer deploy job 用。`viewer-deploy` を entrypoint にする

## Notes

- `ci.yaml` は Artifact Registry の repository を `_ARTIFACT_REPOSITORY`、image 名を `_API_IMAGE` / `_CLI_IMAGE` / `_DB_MIGRATE_IMAGE` / `_ADMIN_IMAGE` / `_VIEWER_IMAGE` で受け取る
- Cloud Run の service / job 名は `stg-*` の値を YAML に直接定義する
- Cloud Run jobs の service account は `_JOB_SERVICE_ACCOUNT` で受け取る
- Admin の `PUBLIC_APP_URL` は Cloud Build substitution の `_PUBLIC_APP_URL` を build arg として渡す
- Viewer deploy job は `API_URL` / `SITE_URL` / Cloudflare Worker 名 / account ID / API token secret を受け取り、Cloudflare Workers へ deploy する
- staging はクロール不要のため `SITE_NOINDEX=true` を渡し、robots.txt と meta robots を noindex にする
- Cloud Build 上で使う tool / base image の版は `mise.toml`（`[tools]` / `[vars]`）を Source of Truth とし、`tools/read-mise-value.sh` 経由で参照する
