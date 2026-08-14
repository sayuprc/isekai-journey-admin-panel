# Dev Infrastructure

開発環境向けビルド定義の入口です

## Cloud Build

- `cloudbuild/ci.yaml`: API / CLI / DB migrate / Admin / Viewer / admin-proxy / Discord Notifier のコンテナを build / push し、Cloud Run service / job を deploy する
- Cloud Build から実行する build command は YAML に直接定義する

## Dockerfiles

- `docker/api/Dockerfile`: Laravel API 用で FrankenPHP を使う
- `docker/cli/Dockerfile`: artisan job 用で PHP CLI と Laravel application を含める
- `docker/db-migrate/Dockerfile`: migration job 用で Atlas と schema 定義のみを含める
- `docker/admin/Dockerfile`: Admin 用で build stage では `bun --filter admin build`、runtime stage では Bun slim image を使う
- `docker/viewer/Dockerfile`: Viewer deploy job 用で `viewer-deploy` を entrypoint にする
- `docker/admin-proxy/Dockerfile`: admin-proxy deploy job 用で `admin-proxy-deploy` を entrypoint にする
- `docker/discord-notifier/Dockerfile`: MoonBit 製 Discord Notifier を native ビルドして載せる

## 初回構築前提

- Cloud Build trigger に dev 用の substitution value を設定する
- Cloud Build service account に Artifact Registry、Cloud Run、Service Account User の必要権限を付与する
- API / Admin service と DB migrate / Admin invite / media youtube import / Viewer deploy / admin-proxy deploy job の runtime env / secret は `ci.yaml` で定義しないため初回デプロイ前に別経路で設定する
- DB migrate job には `DB_USERNAME` / `DB_PASSWORD` / `DB_HOST` / `DB_PORT` / `DB_DATABASE` を設定する
- media youtube import job (`dev-media-youtube-import`) には DB 接続 env と `YOUTUBE_API_KEY` を設定する
- Viewer deploy job (`dev-viewer-deploy`) には `API_URL` / `SITE_URL` / `SITE_NOINDEX=true` / `APP_ENV=development` / Cloudflare Worker 名 / account ID と `CLOUDFLARE_API_TOKEN` secret を設定する
- admin-proxy deploy job (`dev-admin-proxy-deploy`) には `ADMIN_PROXY_WORKER_NAME` / `ADMIN_PROXY_ORIGIN_URL` / `CLOUDFLARE_ACCOUNT_ID` と `CLOUDFLARE_API_TOKEN` / `PROXY_SHARED_SECRET` secret を設定する
- Admin service は `PROXY_SHARED_SECRET` と同じ secret を参照する

## Notes

- `ci.yaml` は Artifact Registry の repository を `_ARTIFACT_REPOSITORY`、image 名を `_API_IMAGE` / `_CLI_IMAGE` / `_DB_MIGRATE_IMAGE` / `_ADMIN_IMAGE` / `_VIEWER_IMAGE` / `_ADMIN_PROXY_IMAGE` / `_DISCORD_NOTIFIER_IMAGE` で受け取る
- Cloud Run の service / job 名は `dev-*` の値を YAML に直接定義する
- Cloud Run jobs の service account は `_JOB_SERVICE_ACCOUNT` で受け取る
- Discord Notifier の runtime service account は `_NOTIFY_SERVICE_ACCOUNT` で受け取る
- Admin の `PUBLIC_APP_URL` は Cloud Build substitution の `_PUBLIC_APP_URL` を build arg として渡す
- Viewer deploy job は runtime env / secret を受け取り、Cloudflare Workers へ deploy する
- admin-proxy deploy job は Cloud Build が `gcloud run jobs deploy --wait` で image 差し替えと実行完了まで行い、Cloudflare Workers へ deploy する
- 環境バッジ表示のため `APP_ENV=development` を Admin は Dockerfile の `ENV` で渡す
- Cloud Build 上で使う tool / base image の版は `mise.toml`(`[tools]` / `[vars]`)を Source of Truth とし、`tools/read-mise-value.sh` 経由で参照する
