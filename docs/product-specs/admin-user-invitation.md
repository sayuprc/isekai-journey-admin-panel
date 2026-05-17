# 管理ユーザー招待運用

## Summary

管理ユーザーの新規登録を「CLI で発行した招待トークンを受領者が登録フォームに入力して自分でアカウントを作成する」フローに統一する。

## Users / Actors

- 既存の管理者（CLI 実行者）: 招待トークンを発行する
- 新規の管理ユーザー（受領者）: 受け取ったトークンで自身の名前 / メール / パスワードを登録する

## Affected Surfaces

- `src/contracts/src/admin/admin-users/`（`POST /admin/v1/admin-users/registrations`）
- `src/server`（`admin:invite` コマンド、`Register` UseCase / Controller / 招待リポジトリ）
- `src/admin/src/pages/auth/register.astro`, `src/admin/src/components/auth/RegisterForm.tsx`

## Core Flow

1. 既存管理者が CLI で `docker compose exec api php artisan admin:invite [--privilege] [--expires-in-hours=24] [permissions...]` を実行する。標準出力に平文トークンと有効期限が表示される。
2. 招待を受領した本人が `https://<admin>/auth/register?token=<発行値>` を開き、名前 / メール / パスワードを入力して送信する。
3. サーバーは `POST /admin/v1/admin-users/registrations` を未認証で受け取り、トークンを HMAC-SHA256 (`APP_KEY` 鍵) でハッシュ化して照合する。成功時は管理ユーザーを作成し、招待を `consumed_at` 付きで残す。
4. 受領者は `/auth/login` で通常のメール / パスワードログインを行う。

## API / Data Notes

- 招待は `admin_user_invitations` テーブルに保存し、平文トークンは保存しない（`token_hash` のみ）。`UNIQUE(token_hash)` で同値衝突を避ける。
- 権限は別テーブル `admin_user_invitation_permissions`（複合 PK）に保存する。
- トークン無効・期限切れ・消費済みはすべて 401 を返す（情報量を絞るため理由は API レスポンスで区別しない）。
- 入力不正（名前空・メール形式不正など）は 422 を返す。

## Acceptance Criteria

- `admin:invite` が平文トークンと有効期限を出力し、DB にはハッシュのみ残ること。
- `/auth/register?token=...` 経由で登録した後、通常ログインで JWT を取得できること。
- 同じトークンでの二回目登録は 401 になること。
