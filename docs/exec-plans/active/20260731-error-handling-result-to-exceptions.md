# エラーハンドリングの Result → 例外移行

## Status

in-progress

## Background

期待される業務エラーを `sayuprc/result-type` の Result で表現し Domain → UseCase → Presenter と伝搬させてきたが、
層をまたぐたびの詰め替え (mapErr / andThen) が server の 131 ファイルに波及し、煩雑さの割に利益が薄い。
VO はコンストラクタで既に throw しており Result 版 `create()` と二重 API になっている。
設計判断は [ADR-0013](../../adr/ADR-0013-use-exceptions-instead-of-result-for-error-handling.md) に記録済み

## Goal

Result を server 全層から撤去し例外ベースへ移行する。
エラーレスポンスは全ステータスで `{code, message, details?}` の統一エンベロープに刷新する

## Scope

- `src/contracts`: エラーレスポンスの再定義 (統一エンベロープ + code enum)
- `src/server`: 例外階層の導入、ハンドラ集約、UseCase / Presenter / VO / CLI の例外化、テスト更新
- `src/admin` / `src/viewer`: エラー消費コードの新形式対応
- `.claude/rules/01-backend.md` ほか関連文書の更新

## Non-Scope

- HTTP ステータス割当の変更 (業務ルール違反の 409 化などはしない)
- Presenter / Converter の統合や層構造の再編
- リソース別エラー code (`song.not_found` 等) の導入
- 503 / 504 の契約変更 (ボディなしのまま)

## Acceptance Criteria

- 全 API エラー (401/403/404/422/400/500) が `{code, message, details?}` を返す
- 422 が複数 field のエラーを一括で報告する
- `src/server` に `ResultType\` への参照が残っていない (grep で 0 件)
- composer.json から `sayuprc/result-type` が消えている
- VO の構築が public コンストラクタのみになり `create()` / `reconstruct()` が存在しない
- Console コマンドが既知例外で友好的メッセージ + 非ゼロ exit code を返す
- `mise run api:ecs` / `api:phpstan` / `api:arkitect` / `api:test`、`contract:test` / `contract:compile:*`、admin / viewer のテストがすべて green
- `.claude/rules/01-backend.md` の「業務エラーは Result で返す」規約が例外方針に置き換わっている

## Steps

### Stage 1: ワイヤ刷新 (内部は Result のまま)

- [x] contracts: `shared/response.tsp` に code enum と統一エンベロープを定義し、各 service.tsp のエラーレスポンスを置換、OAS と生成コードを再生成する
- [x] server: `ResolvesUseCaseError` trait を新形式へ書き換え (401/403 にボディ付与、422 は details へ)、`bootstrap/app.php` に 500 のエンベロープ描画を追加する
- [x] admin: `server/errors.ts` / `utils/form-error.ts` などのエラー消費を新形式へ更新する
- [x] viewer: エラー消費箇所を調査し新形式へ更新する (形式依存の消費なし、生成型の更新のみ)
- [x] server / admin / viewer の feature テストを新形式へ更新し全検証を通す

### Stage 2: 内部例外化 (ワイヤ不変)

- [x] Support 基盤: 例外階層 (UseCase 例外 + ドメイン例外)、ハンドラの match 表 (例外クラス → {code, status})、InputData→VO 組立て役の仕組み、CLI 用 catch trait を導入する
- [x] VO 基盤: 基底クラスのコンストラクタを public 化する (create / reconstruct は移行完了まで温存)
- [x] パッケージ単位で例外化する: Auth (+AdminUser) → Song → Media → Release → Person / SiteStats / AuditLog の順に UseCase / Presenter / Controller / Command とテストを移行する

### 仕上げ

- [x] 基底から `create()` / `reconstruct()` を削除し、`UseCaseError` 群・`Support\Domain\Error` の Result 用エラー型を削除、composer から `sayuprc/result-type` を除去する
- [x] `.claude/rules/01-backend.md` の業務エラー規約を例外方針へ書き換え、近接文書 (ARCHITECTURE.md 等) を点検する

## Decision Log

- 2026-07-31: グリリングで設計合意 (詳細は ADR-0013)。Result 全層撤去、例外→HTTP はハンドラ集約 + match 表 1 枚、ドメイン例外も直接レンダリング、統一エンベロープ + カテゴリ単位 code 6 個、422 は Application 層の組立て役が全 field 集約、VO は public コンストラクタ一本化、CLI は共通 trait で catch、Presenter は残してシグネチャ変更のみ、ステータス割当不変、500 もエンベロープ化
- 2026-07-31: 移行はワイヤ先行 2 段階。Stage 1 でクライアントに見える変更を閉じ込め、Stage 2 はパッケージ単位で Result 版と共存させながら例外化する
- 2026-07-31: VO の `create()` / `reconstruct()` は Stage 2 期間中は温存し、全パッケージ移行後の仕上げで削除する (基底の一斉削除は big bang になるため)
- 2026-07-31: エンベロープの組み立ては `App\Http\Responses\ApiError` に一元化した。trait のほか、直接エラーを返していた `OpenApiValidator` / `Authenticate` ミドルウェアと `RefreshPresenter` (ボディなし 401 を返していた) もここへ寄せた。Stage 2 のハンドラ match 表も同クラスを使う
- 2026-07-31: contracts の service.tsp は共有ラッパーモデル参照のため変更不要だった。エンベロープ変更は `shared/response.tsp` のみで完結
- 2026-07-31: admin の `bun test` の 4 件の失敗 (astro:env/server 解決エラー) は本変更前から存在する既知の問題で今回の範囲外
- 2026-07-31: 422 の担い手は `DomainValidationException` に 1 本化し、集約用の `FieldErrors` とともに `Support\Domain` に配置した (当初案の UseCase 層 ValidationFailedException は廃止)。IntegrityService などドメインサービスも同じ仕組みで field 集約するため
- 2026-08-01: `FieldErrors` (インスタンス生成 + check + throwIfFailed の 3 段プロトコル) を `Field::of` + `Fields::validate` に置き換えた。throwIfFailed 忘れで検証失敗が握りつぶされる余地をなくし、`Field<T>::value()` で構築済み VO を型付きで取り出すことで validate 通過後の再構築 (二重 new) も廃止するため。なお固定アリティ validate1..N + 分割代入で Field 変数を消す案は検討の上、ボイラープレートと位置依存を嫌って見送った (全 field の試行結果を揃える合流点として変数受けは維持)
- 2026-07-31: TextValueObject の NFC 正規化は create/reconstruct 削除に伴いコンストラクタへ移設。基底に `@phpstan-consistent-constructor` を付与
- 2026-07-31: 422 の field 名は VO クラス FQCN から論理名 (title, email 等) へ変更。FQCN はクライアントが利用不能でありワイヤ改善として許容 (Stage 2「ワイヤ不変」からの軽微な逸脱)。あわせて複数 field の一括報告が有効になった
- 2026-07-31: Authenticate/Refresh/Login/RecoveryFinish 系の失敗は UnauthenticatedException に集約 (旧実装でも最終的に 401)。RegisterStart/Finish はユーザー列挙防止のため固定メッセージの BusinessRuleViolationException へ詰め替え、旧 Presenter の squash を UseCase に移設
- 2026-07-31: RegistrationTokenConsumeService / RecoveryCodeVerifyService / JwtHandler の verify は「期待される不在」を表すため例外ではなく nullable 戻り値に変更
- 2026-07-31: mago lint の指摘 (101 errors) は移行前 (102 errors) から増えておらず既存負債と判断
- 2026-07-31: code-reviewer レビュー (3 並列) の指摘に対応。(1) JwtHandler::verify が ExpiredException しか捕捉せず改ざんトークン等で 500 になる問題を修正 (DomainException|InvalidArgumentException|UnexpectedValueException を捕捉、回帰テスト 2 件追加。旧実装から潜在していた不具合)、(2) テスト変換で生じた expectException 後の到達不能アサーション 11 箇所を try/catch 形式へ復元 (ロールバック検証を含む)、(3) Refresh の不正形式 ID → 401 経路にユニットテスト追加、(4) TrackTest の冗長な代入を整理。Song Get の resourceName '楽曲' と Update/Delete の 'Song' の表記揺れは移行前からの既存挙動のため温存

## Validation

- `mise run api:ecs` / `api:phpstan` (level 10) / `api:arkitect` / `api:test` (603 tests, skip 3 は既存) すべて green
- `src/server` から `ResultType\` への参照 0 件、composer から `sayuprc/result-type` 除去済み
- Stage 1: `contract:format:check` / `contract:test` / `contract:compile:*`、admin lint/style/format/build、viewer format/lint/build すべて green (PR #909 でマージ済み)
