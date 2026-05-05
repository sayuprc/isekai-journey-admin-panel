# Admin Passkey Auth

## Status

planned

## Background

管理画面の認証は現状 `email + password` 前提で実装されている。今回の変更では、管理ユーザー登録を特定の利用者だけに制限するための登録トークンを導入し、登録時にパスキーも合わせて登録する。既存のパスワード認証は最終的に廃止する。

## Goal

管理画面の認証を `email -> passkey` に置き換え、CLI で発行した登録トークンを持つ利用者だけが管理ユーザー登録できる状態にする。

## Scope

- `src/server` の認証、登録、CLI bootstrap、永続化の変更
- `src/contracts` の認証 API 契約の変更
- `src/admin` の登録画面、ログイン画面、BFF の変更
- WebAuthn 用設定と検証の追加
- 関連テストとドキュメントの更新

## Non-Scope

- 既存パスワードユーザーの移行互換
- メール送信や SMS 送信を使った OTP 配布
- パスキー以外の認証手段の併用
- 招待トークンの URL 配布導線

## Acceptance Criteria

- CLI から `email`, `name`, `role`, `permissions`, `expires_at` を持つ登録トークンを発行できる
- 登録画面は `email + registration token` を `POST` で受け取り、WebAuthn 登録を完了できる
- 登録成功時に `admin_users` と passkey 関連データが保存され、登録トークンは再利用できない
- ログイン画面は `email -> passkey` で認証し、既存の access token / refresh token セッションを開始できる
- password 前提の API 契約、サーバー実装、管理画面 UI が除去される
- 無効 token、使用済み token、期限切れ token、credential 重複、challenge 再利用をテストで検証できる

## Steps

1. `admin_user_registration_tokens` と `admin_user_passkeys` のスキーマを追加する
2. CLI bootstrap として `admin:invite` を実装し、平文トークンは実行時のみ表示して DB にはハッシュだけ保存する
3. `src/contracts` で登録開始、登録完了、ログイン開始、ログイン完了の API 契約を追加し、password ログイン契約を削除する
4. サーバーに登録トークン検証、challenge 管理、WebAuthn 登録完了、WebAuthn ログイン完了の use case を追加する
5. `src/admin` に登録画面と `email -> passkey` ログイン画面を実装し、BFF から WebAuthn API を中継する
6. password 前提のコントローラ、provider、repository、use case、フォーム、型を除去する
7. Unit / Integration / Feature テストを追加し、README など必要な運用ドキュメントを更新する

## Decision Log

- 2026-05-05: 登録制御は短い OTP ではなく長いランダム登録トークンを採用する
- 2026-05-05: 登録トークンは URL クエリに載せず、登録フォームから `POST` 送信する
- 2026-05-05: ログインフローは `email -> passkey` を採用する
- 2026-05-05: 既存 password ユーザーとの互換は考慮せず、新方式へ置き換える
- 2026-05-05: bootstrap は CLI で完結させ、role / permissions を登録トークン発行時に固定する

## Validation

- Atlas schema 適用後にテーブル定義と制約を確認する
- 契約変更後に生成物を更新し、contract compile 系の確認を通す
- サーバー側は認証と登録の Unit / Integration / Feature テストを通す
- 管理画面側は登録とログインの BFF / UI の検証を行う
