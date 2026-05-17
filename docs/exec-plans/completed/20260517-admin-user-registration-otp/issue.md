# Execution Plan — Issue

## Title

CLI 発行の登録トークンを用いた管理画面ユーザー登録導線

## Background

パスキー実装を進める前段として、まずは WebUI から管理画面ユーザーを登録できる状態が必要である。現状の管理画面は `src/admin/src/pages/auth/login.astro` / `src/admin/src/components/auth/LoginForm.tsx` を使ったログイン導線と、`src/server/routes/admin.php` の `/auth/login` API しか公開されておらず、WebUI から新規の管理ユーザーを作成する導線がない。

一方でサーバー側には `src/server/app/Console/Commands/AdminUser/CreateCommand.php` と `src/server/packages/AdminUser/Application/Cli/UseCase/Create/CreateUseCase.php` による CLI 作成機能があり、管理ユーザー作成のドメイン知識自体はすでに存在する。ただしこれは管理者が直接ユーザー情報とパスワードを投入する前提であり、「許可したユーザーのみが登録できる」ための招待・ワンタイム登録トークンの仕組みは未実装である。

## Goal

管理者が CLI から一度だけ使える登録トークンを発行し、その登録トークンを入力したユーザーだけが `auth` 配下の WebUI 登録フォームから管理画面ユーザーを作成できるようにする。これにより、管理ユーザー登録を WebUI に移しつつ、許可していない自己登録を防げる状態を作る。

## Scope

- 認証配下の登録 API 契約追加と関連生成物の更新: `src/contracts/src/admin/auth/*`, `src/admin/src/generated`, `src/server/Generated`
- 認証まわりの公開導線追加: `src/server/routes/admin.php`, `src/admin/src/server/routes/auth.ts`, `src/admin/src/server/index.ts`
- 登録トークンを伴う登録サーバー実装と既存 CLI 作成機能の再利用整理: `src/server/app/Console/Commands/AdminUser/CreateCommand.php`, `src/server/routes/console.php`, `src/server/packages/AdminUser/*`, `src/server/app/Http/Controllers/Api/Admin/V1/Auth/*`
- 登録トークンの発行・検証・失効を扱う永続化または設定レイヤーの追加: `src/server/database/atlas/schemas/*`, `src/server/app/Providers/Domain/*` を含む関連モジュール
- 登録フォームと未認証導線の追加: `src/admin/src/pages/auth/login.astro`, `src/admin/src/components/auth/LoginForm.tsx` に隣接する `src/admin/src/pages/auth/*` / `src/admin/src/components/auth/*`
- サーバー・管理画面それぞれの検証追加: `src/server/tests/Feature`, `src/server/tests/Integration`, `src/server/tests/Unit`, `src/admin/src/server/client.test.ts`

## Non-Scope

- パスキー自体の登録・認証フロー実装
- 既存のメールアドレス + パスワードによるログイン方式の全面置き換え
- 登録トークンのメール送信や外部通知連携
- 管理ユーザーの自己申請、承認ワークフロー、招待一覧 UI などの運用機能

## Acceptance Criteria

- 管理者が CLI から登録トークンを発行でき、少なくとも一般/特権の別を指定したうえで登録に必要なワンタイム値を取得できる
- 未認証ユーザーが WebUI の登録フォームから名前・メールアドレス・パスワード・登録トークンを送信できる
- サーバーは登録トークンが有効な場合のみ管理ユーザーを作成し、使用済みまたは無効な登録トークンでは登録を拒否する
- 登録トークンを使った登録導線は、既存の管理ユーザー作成ドメインロジックと整合し、重複したユーザー作成ルールを新設しない
- 登録 API は `auth` 配下に追加され、既存の `admin-users` API に新しい登録責務を持ち込まない
- 管理画面 BFF / API / CLI の追加部分に対するテストが追加され、関連する静的検査またはテストで成立を確認できる
