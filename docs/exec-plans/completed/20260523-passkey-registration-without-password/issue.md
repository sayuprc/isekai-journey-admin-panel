# Execution Plan — Issue

## Title

管理ユーザー登録時のパスキー同時登録とパスワード廃止

## Background

PR #716(`Add admin passkey auth flow`)は `feature/passkey-auth-phase1-pr` から `feature/passkey` へ MERGED されている。一方、現在のローカル `feature/passkey` / `origin/feature/passkey` は `a0c02771 Merge pull request #756 from sayuprc/feature/admin-user-register-with-token` の状態で、`rg passkey|webauthn` では passkey / WebAuthn 実装が見えていない

追加調査では `git fetch origin pull/716/head:refs/remotes/origin/pull/716/head` により PR #716 head を取得済みで、`origin/pull/716/head` は `4362cb86 Fix Arkitect violations in passkey auth` を指し、`6509a11a Add admin passkey auth flow` や `6a42c7c5 Merge pull request #715 from sayuprc/passkey-foundation` を含む。ただし `origin/pull/716/head` は現在の `HEAD` の祖先ではなく、`HEAD..origin/pull/716/head` には passkey 以外の古い履歴差分も多く含まれる。そのため、PR #716 head は passkey 実装の参照元として使うが、merge / cherry-pick などでコミット履歴ごと取り込むことは避け、必要部分を現行 `feature/passkey` の構成へ再適用する

今後は管理ユーザーをパスワードではなく passkey で認証する方針にするため、招待トークンを使った初回登録時に passkey 登録も完了させ、パスワード入力・保存を登録フローから取り除く必要がある。ログイン機能は後続で整える前提とし、本タスク中に一時的に既存ログイン導線が壊れることは許容する

## Goal

登録トークンを使った管理ユーザー登録で、ユーザー作成と passkey credential の登録を同じ成功フローとして完了できるようにする。登録 API / BFF / UI から password 入力を廃止し、サーバー側も新規管理ユーザー登録時にパスワードを必須にしない状態へ移行する

## Scope

- `origin/pull/716/head` に含まれる passkey 基盤 / API / UI を参考元とした、現行構成への必要部分の再適用
- コントラクト
  - `src/contracts/src/admin/auth/domain.tsp`
  - `src/contracts/src/admin/auth/service.tsp`
  - `src/contracts/src/admin/auth/transport.tsp`
  - `POST /auth/register` の request から `password` を外し、passkey 登録完了に必要な WebAuthn credential 情報を受け取れる形へ変更する
- サーバ HTTP / Application 層
  - `src/server/app/Http/Controllers/Api/Admin/V1/Auth/RegisterController.php`
  - `src/server/packages/AdminUser/Application/Admin/UseCase/Register/RegisterInputData.php`
  - `src/server/packages/AdminUser/Application/Admin/UseCase/Register/RegisterUseCase.php`
  - `src/server/app/Http/Presenters/Api/Admin/V1/Auth/RegisterPresenter.php`
  - 登録トークン検証、`AdminUser` 作成、登録トークン消費、passkey credential 永続化を登録成功時の一連の処理として扱う
- サーバ Domain / Infrastructure / DB
  - `src/server/packages/AdminUser/Domain/Models/AdminUserRepositoryInterface.php`
  - `src/server/packages/AdminUser/Infrastructures/AdminUserRepository.php`
  - `src/server/database/atlas/schemas/admin-users.my.hcl`
  - `origin/pull/716/head` に含まれる passkey credential 永続化関連の Domain / Infrastructure / DB スキーマを参考にした現行構成への再適用
  - 新規登録時に password を保存しない、または password カラムに依存しない形へ移行し、passkey credential を管理ユーザーに紐づけて保存する
- 管理画面 UI / BFF
  - `src/admin/src/components/auth/RegisterForm.tsx`
  - `src/admin/src/pages/auth/register.astro`
  - `src/admin/src/server/routes/auth.ts`
  - `src/admin/src/utils/webauthn.ts`
  - 登録フォームからパスワード欄を削除し、ブラウザの WebAuthn ceremony を使って passkey を登録してから BFF/API に送信する
- 生成物とテスト
  - `src/contracts/generated/oas/IsekaiObservatory.Admin.v1.yaml`
  - `src/admin/src/generated/`
  - `src/server/Generated/`
  - 関連する API feature test、use case unit test、BFF test、UI 側の型/ビルド対象

## Non-Scope

- passkey ログイン導線を完成させること(既存ログインが一時的に壊れることは許容する)
- パスワードログインとの互換性維持、移行期間の dual auth 対応
- 既存管理ユーザーの passkey 移行・再登録導線
- `admin:invite` による登録トークン発行仕様の変更
- 招待メール送信、トークン再発行、トークン一覧/失効 UI
- Viewer 側(`src/viewer/`)の変更
- PR #716 のコミット履歴を merge / cherry-pick 等でそのまま取り込むこと

## Acceptance Criteria

- 管理画面の登録フォームにパスワード入力欄が表示されず、登録トークン・email・名前とブラウザの passkey 登録操作で登録を送信できる
- 有効な登録トークン、対象 email、名前、WebAuthn credential を送信したとき、API が成功し、`admin_users` に該当 email のユーザーが作成され、`admin_user_registration_tokens.status` が `Consumed` になり、`admin_user_passkeys` にそのユーザーへ紐づく credential が 1 件保存される
- 登録成功時に `admin_users.password` へ新しい平文由来のハッシュが保存されず、`admin_users` スキーマから password カラムが削除されている
- `RegisterRequest` の contract / generated client / generated PHP SDK から登録用 `password` が削除され、管理画面 BFF の `/auth/register` も password を受け取らない
- passkey credential の検証に失敗した場合、管理ユーザー作成、登録トークン消費、passkey 永続化のいずれも行われず、API/BFF/UI で検証可能なエラーになる
- 未知のトークン、email 不一致、期限切れ、消費済みトークン、email 衝突、名前などの入力バリデーション違反では、管理ユーザー作成、登録トークン消費、passkey 永続化のいずれも行われない
- 既存の `admin:invite` と登録トークン検証の挙動は、password 廃止以外で回帰しない
