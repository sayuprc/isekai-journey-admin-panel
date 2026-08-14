# 管理画面の監査ログ閲覧画面

## Status

completed

## Background

`20260505-audit-log.md` で監査ログ基盤(`audit_logs` テーブル、`Support\UseCase\AuditLog\AuditLogRecorder` と各書き込み系 UseCase への組み込み)は完了済みである
しかし現状、記録された監査ログを参照する手段は DB 直接アクセスしかなく、運用上の調査(誰がいつ何を作成・更新・削除したか等)を管理者が画面から行えない
不正操作・誤操作の事後追跡や運用上の事実確認のため、管理画面に監査ログ閲覧 UI を追加する必要がある

## Goal

管理画面に監査ログの一覧画面と詳細画面を追加し、`ReadAuditLog` 権限を持つ管理ユーザーが、期間・action・target_type・target_id・admin_user_id で絞り込んで監査ログを閲覧できるようにする

## Scope

- 権限定義
  - `AdminUser\Domain\Models\Permission` に `ReadAuditLog`(value: `read_audit_log`、表示名: `監査ログ閲覧`)を追加する
- バックエンド(読み取りのみ)
  - パッケージ配置: 監査ログ閲覧は横断的関心事のため、基盤と同じ `Support` パッケージ配下に置く
    - `src/server/packages/Support/UseCase/AuditLog/Query/AuditLogQueryServiceInterface.php`(一覧取得＋件数取得、絞り込み条件 DTO)
    - `src/server/packages/Support/UseCase/AuditLog/Query/AuditLogSummary.php`(一覧 1 行分の DTO)
    - `src/server/packages/Support/UseCase/AuditLog/Search/SearchUseCase.php`(一覧)
    - `src/server/packages/Support/UseCase/AuditLog/Get/GetUseCase.php`(詳細)
    - 各 UseCase は `UseCaseAuthorizer` で `Permission::ReadAuditLog` を要求する
    - `src/server/packages/Support/Infrastructures/Query/AuditLog/EloquentAuditLogQueryService.php` に Eloquent 実装を追加
  - ルート: `src/server/packages/Support/Route/AuditLogRouteMap.php` を新設し、`app/Providers` で登録する(既存の `Song`/`Person` の RouteMap を踏襲)
  - Controller: `src/server/app/Http/Controllers/Admin/AuditLog/` 配下に Search / Get の 2 本
- API 契約 (`src/contracts/src/admin/audit-logs/`)
  - `domain.tsp` / `service.tsp` / `transport.tsp` / `main.tsp` を `persons` 等の既存ディレクトリに倣って追加
  - エンドポイント:
    - `GET /admin/audit-logs/search`: `from`, `to`(`created_at` 範囲), `action`, `target_type`, `target_id`, `admin_user_id`, `page`, `per_page` を query で受ける一覧
    - `GET /admin/audit-logs/{auditLogId}`: 詳細取得
  - レスポンスの `snapshot` フィールドは任意 JSON(Record/object)として表現する
- 管理画面 (`src/admin`)
  - 一覧ページ: `src/admin/src/pages/audit-logs/index.astro`(絞り込みフォーム＋一覧テーブル＋ページネーション)
  - 詳細ページ: `src/admin/src/pages/audit-logs/[id].astro`(ヘッダーに 誰が・いつ・action・target_type・target_id を整形表示、`snapshot` は JSON 整形して `<pre>` で生表示)
  - サイドナビに「監査ログ」項目を追加する(権限ベースの出し分けは行わず、全管理ユーザーに表示する。アクセス制御は API 側の `Forbidden` (403) で担保する)
- テスト
  - Search / Get UseCase の Integration テスト(権限ありで取得できる、権限なしで `Forbidden`、絞り込みが機能する)
  - Controller の Feature テスト(`/admin/audit-logs/search`, `/admin/audit-logs/{id}` の代表ケース)

## Non-Scope

- 監査ログの編集・削除・再記録(書き込み系機能は一切追加しない)
- 閲覧サイト (`src/viewer`) への機能追加
- `target_type` ごとの `snapshot` 整形ビュー(集約構造の変更でビューが壊れるため、JSON 生表示に統一する)
- 監査ログの CSV/JSON エクスポート
- 監査ログ自体に対する監査ログ記録(無限ループになるため)
- 既存 `audit_logs` テーブルのスキーマ変更(前段の Atlas スキーマをそのまま使う)
- 全文検索や `snapshot` 内部のキー検索
- 既存パッケージ(`Person` / `Song` 等)への変更

## Acceptance Criteria

- `Permission::ReadAuditLog` が enum に追加され、`getName()` が `監査ログ閲覧` を返す
- `ReadAuditLog` を持たない管理ユーザーが `GET /admin/audit-logs/search` または `GET /admin/audit-logs/{id}` を叩くと、`Forbidden` (403) が返る
- `ReadAuditLog` を持つ管理ユーザーが `GET /admin/audit-logs/search` を叩くと、`audit_logs` の行が `created_at` 降順でページングされて返る
- `from` / `to` を指定すると `created_at` がその範囲内の行のみ返る
- `action` / `target_type` / `target_id` / `admin_user_id` を指定すると、その値に一致する行のみ返る(複数指定は AND)
- `GET /admin/audit-logs/{id}` は、対象 1 件の `auditLogId`, `adminUserId`, `action`, `targetType`, `targetId`, `snapshot`, `createdAt` を返し、存在しない場合は `NotFound` (404) を返す
- 管理画面 `/audit-logs` で一覧が表示され、絞り込みフォームの値が API クエリに反映される
- 管理画面 `/audit-logs/{id}` でヘッダー情報が整形表示され、`snapshot` が JSON 整形(インデント付き)で `<pre>` 表示される
- サイドナビには全管理ユーザーに「監査ログ」項目が表示され、権限のないユーザーがクリックしても API が 403 を返してエラー表示になる(権限ベースの導線出し分けは今回の Scope 外)
- `mise run api:ecs` / `api:phpstan` / `api:arkitect` / `api:test` がすべて緑で完走する

## Steps

1. ✅ **`Permission::ReadAuditLog` を追加する**
   - 修正: `src/server/packages/AdminUser/Domain/Models/Permission.php`
     - `case ReadAuditLog = 'read_audit_log';` を末尾に追加
     - `getName()` の `match` に `self::ReadAuditLog => '監査ログ閲覧'` を追加
   - 既存の `Permissions` 値オブジェクト・シーダ等で全 `Permission` ケースを列挙している箇所がないか `grep -r 'Permission::' src/server` で確認し、必要なら追従させる(基本は enum なので追加だけで足りる想定)

2. ✅ **TypeSpec に `audit-logs` ディレクトリを追加する**
   - 新規: `src/contracts/src/admin/audit-logs/{domain.tsp,service.tsp,transport.tsp,main.tsp}`
     - `domain.tsp`: `auditLogId`(uuid 派生 scalar)、`AuditAction` enum (`Create|Update|Delete|Refresh`、value は文字列で server 側の `AuditAction` と揃える)、`AuditTargetType` enum (`AdminUser|Person|Song|SongTag`)、`AuditLogSummary`(`auditLogId`, `adminUserId`, `action`, `targetType`, `targetId`, `createdAt`)、`AuditLog`(Summary に `snapshot: Record<unknown>` を加える)
     - `transport.tsp`: `AuditLogSearchResponse { auditLogs: AuditLogSummary[]; maxPage: int32; }`、`AuditLogGetResponse { auditLog: AuditLog; }`
     - `service.tsp`: `@route("/audit-logs")` interface `AuditLogService`。`@route("/search") searchAuditLogs(@query from?: utcDateTime, @query to?: utcDateTime, @query action?: AuditAction, @query("target_type") targetType?: AuditTargetType, @query("target_id") targetId?: uuid, @query("admin_user_id") adminUserId?: uuid, @query page?: page = 1, @query("per_page") perPage?: PerPage = PerPage.fifty): Ok<AuditLogSearchResponse> | Unauthorized | Forbidden | ServerError;` と `getAuditLog(@path auditLogId: uuid): Ok<AuditLogGetResponse> | Unauthorized | Forbidden | NotFound | ServerError;`。`@useAuth(BearerAuth)` を付与
     - `main.tsp`: 3 ファイルを import
   - `src/contracts/src/admin/main.tsp` に `import "./audit-logs";` を追加(既存 `songs`/`persons` の import に倣う)
   - 生成は手動で行わない方針を Decision Log に明記。後続 Step で `mise run admin:generate` → `mise run contract:compile:admin` → `src/contracts/generated/oas/` 再生成、`src/admin/src/generated/` 再生成を行う

3. ✅ **OAS / 管理画面クライアントを再生成する**
   - 実行: `mise run contract:format:check` で `.tsp` のフォーマット確認 → `mise run contract:compile:admin` で OAS 生成 → `mise run admin:generate` で管理画面 API クライアント生成
   - 生成物 (`src/contracts/generated/oas/admin.yaml`、`src/admin/src/generated/`) は手動編集禁止

4. ✅ **`Support\UseCase\AuditLog\Query\` に Query 層を追加する**
   - 新規: `src/server/packages/Support/UseCase/AuditLog/Query/AuditLogSearchCriteria.php`
     - `from: ?CarbonImmutable`、`to: ?CarbonImmutable`、`action: ?AuditAction`、`targetType: ?AuditTargetType`、`targetId: ?string`、`adminUserId: ?string`、`page: int`、`perPage: PerPage` の readonly DTO。`Support\Optional\Some|None` を使う既存パターンが Song/Person で採用されているならそれに合わせる(`SongSearchCriteria` を確認した結果に従う)
   - 新規: `src/server/packages/Support/UseCase/AuditLog/Query/AuditLogSummary.php`
     - readonly: `auditLogId, adminUserId, action: AuditAction, targetType: AuditTargetType, targetId, createdAt: CarbonImmutable`
   - 新規: `src/server/packages/Support/UseCase/AuditLog/Query/AuditLogDetail.php`
     - Summary + `snapshot: array<string, mixed>`
   - 新規: `src/server/packages/Support/UseCase/AuditLog/Query/AuditLogQueryServiceInterface.php`
     - `search(AuditLogSearchCriteria): array<AuditLogSummary>`、`maxPage(AuditLogSearchCriteria): int`、`find(string $auditLogId): ?AuditLogDetail`
   - 注: `SupportComponent::Application` の新設は行わず、Query 層も `SupportComponent::UseCase` 配下に同居させる。既存の `SupportComponent::UseCase` の deps(`Support\Domain`, `Support\Contracts`, `AdminUser\Domain`, ResultType)でそのまま動く

5. ✅ **Search / Get UseCase を `Support\UseCase\AuditLog\` に追加する**
   - 新規: `src/server/packages/Support/UseCase/AuditLog/Search/{SearchInputData,SearchOutputData,SearchUseCase}.php`
     - `SearchInputData`: `from?, to?, action?, targetType?, targetId?, adminUserId?, page=1, perPage=PerPage::Fifty`
     - `SearchOutputData`: `auditLogs: array<AuditLogSummary>`, `maxPage: int`
     - `SearchUseCase`: コンストラクタに `UseCaseAuthorizer` と `AuditLogQueryServiceInterface`。`handle()` で `authorizer->require(Permission::ReadAuditLog)->andThen(...)` し、Criteria 生成 → query 呼び出し → `Ok(SearchOutputData)`
   - 新規: `src/server/packages/Support/UseCase/AuditLog/Get/{GetInputData,GetOutputData,GetUseCase}.php`
     - `GetInputData`: `auditLogId: string`
     - `GetOutputData`: `auditLog: AuditLogDetail`
     - `GetUseCase`: 認可チェック後、`query->find($auditLogId)` が null なら `Err(NotFound)`、それ以外 `Ok(GetOutputData)`。`UseCaseError` の既存 NotFound バリエーションを使う(`Song\GetUseCase` を参考にする)

6. ✅ **Eloquent 実装と Provider 登録を追加する**
   - 新規: `src/server/packages/Support/Infrastructures/Query/AuditLog/EloquentAuditLogQueryService.php`
     - 依存: `App\Models\AuditLog` (既存)、`UuidConverterInterface`
     - `search()`: `AuditLog::query()->when(...)` で各 criteria を AND 結合。`created_at desc` 順、`forPage($page, $perPage->value)` で結果を取得し `AuditLogSummary` 配列へ詰め替え
     - `maxPage()`: 同条件で `count()` し `(int) ceil($count / $perPage->value)` を返す(最小 1 で `Song\Infrastructures\SongQueryService` の実装に合わせる)
     - `find()`: `AuditLog::find($id)` (binary uuid 変換は `UuidConverter` を介す) → snapshot は cast 済み array をそのまま `AuditLogDetail` に詰める
   - 修正: `src/server/app/Providers/Domain/SupportServiceProvider.php`
     - `bind(AuditLogQueryServiceInterface::class, EloquentAuditLogQueryService::class)` を追加
     - `bind(SearchInputData::class, fn () => $this->getMapper()->map(SearchInputData::class, $request->query()))` の Audit ログ版を追加(`Support\UseCase\AuditLog\Search\SearchInputData` を `audit_log` namespace で衝突しないよう `as AuditLogSearchInputData` でエイリアス)
     - 同様に `Support\UseCase\AuditLog\Get\GetInputData` を `auditLogId` ルートパラメータからマップ

7. ✅ **RouteMap・Controller・Presenter・ルート登録を追加する**
   - 新規: `src/server/packages/Support/Route/AuditLogRouteMap.php`(`enum AuditLogRouteMap: string { case Search = 'audit-logs.search'; case Get = 'audit-logs.show'; }`)
   - 新規: `src/server/app/Http/Controllers/Admin/AuditLog/{SearchAuditLogController,GetAuditLogController}.php`
     - `App\Http\Controllers\Api\Song\SearchSongController` を踏襲し、`SearchUseCase` と `SearchPresenter` を DI。`Get` も `GetSongController` を踏襲
     - 注: 既存の Song / Person Controllers は `App\Http\Controllers\Api\` 配下のため、`Admin/AuditLog/` ではなく `Api/AuditLog/` に揃えるか方針を Decision Log で確定。本計画では既存パターンに合わせて `App\Http\Controllers\Api\AuditLog\` 配下に置く
   - 新規: `src/server/app/Http/Presenters/Api/AuditLog/{SearchPresenter,GetPresenter,Converter}.php`
     - `SearchPresenter`: `OpenAPI\Client\Model\AuditLogSearchResponse` に詰める。`Converter` で `AuditLogSummary` → 生成 OAS Model 変換
     - `GetPresenter`: `AuditLogGetResponse` に詰める。snapshot は assoc array をそのまま `setSnapshot($detail->snapshot)`(OAS の `Record<unknown>` は `array<string, mixed>` にマップされる前提を確認)
     - エラーハンドリングは `ResolvesUseCaseError` を use する(既存 SongTag/Song の Presenter に倣う)
   - 修正: `src/server/routes/admin.php`
     - `Authenticate::class` middleware group の中に `Route::prefix('audit-logs')->group(function () { Route::get('/search', [SearchAuditLogController::class, 'handle'])->name(AuditLogRouteMap::Search); Route::get('/{auditLogId}', [GetAuditLogController::class, 'handle'])->name(AuditLogRouteMap::Get); });` を追加

8. ✅ **管理画面の一覧／詳細ページとサイドナビ導線を追加する**
   - 新規: `src/admin/src/pages/audit-logs/index.astro`
     - `Layout` を使い、SolidJS コンポーネント `<SearchList client:only="solid-js" />` を読み込む
   - 新規: `src/admin/src/components/audit-log/SearchList.tsx`
     - `src/admin/src/components/song/SearchList.tsx` を参考に、絞り込みフォーム(`from`、`to`(datetime-local)、`action` select、`targetType` select、`targetId`、`adminUserId`、`page`、`perPage`)と一覧テーブル、ページネーションを実装。**「適用」ボタン押下で `URLSearchParams` を更新 → 再 fetch** する(リアルタイム反映ではない)
     - API 呼び出しは `src/admin/src/generated/` の自動生成クライアント `searchAuditLogs` を使う
   - 新規: `src/admin/src/pages/audit-logs/[id].astro`
     - `Astro.params.id` を `<DetailView client:only="solid-js" auditLogId={id} />` に渡す
   - 新規: `src/admin/src/components/audit-log/DetailView.tsx`
     - ヘッダーに `誰が (adminUserId) / いつ (createdAt) / action / targetType / targetId` を整形表示
     - `snapshot` は `JSON.stringify(value, null, 2)` で整形し `<pre>` で生表示
     - 404 は `not found` 表示
   - 修正: `src/admin/src/components/Sidebar.tsx`
     - `navSections` の「管理」セクション末尾に `{ href: '/audit-logs', label: '監査ログ', icon: <既存 ShieldIcon を流用または新規 SVG> }` を追加する
     - 権限ベースの出し分けは今回行わない。全管理ユーザーに表示する
   - `ReadAuditLog` を持たないユーザーが項目をクリックすると API が 403 を返すので、一覧/詳細コンポーネントは「権限がありません」エラー表示にフォールバックする(既存 SearchList のエラー表示パターンを踏襲)

9. ✅ **インテグレーションテスト・フィーチャーテストを追加する**
   - 新規: `src/server/tests/Integration/Support/UseCase/AuditLog/Search/SearchUseCaseTest.php`
     - `DatabaseTestCase` 継承。`audit_logs` に複数行 seed → `ReadAuditLog` を持つ AdminUser で全件、各 criteria(from/to/action/target_type/target_id/admin_user_id)の AND を検証
     - 権限なし AdminUser で `Forbidden` を検証
     - `created_at` 降順とページングを検証(`per_page=2` で 2 ページ目を取得するなど)
   - 新規: `src/server/tests/Integration/Support/UseCase/AuditLog/Get/GetUseCaseTest.php`
     - 取得成功、`NotFound`、`Forbidden` の 3 ケース
   - 新規: `src/server/tests/Feature/Api/AuditLog/SearchAuditLogTest.php` と `GetAuditLogTest.php`
     - `tests/Feature/Api/Song/SearchSongTest.php` を踏襲。HTTP レイヤで 200/403/404 のステータスとレスポンス JSON 構造を検証
     - `snapshot` に任意 JSON が透過に返ることを検証
   - Unit テストは Query Service のロジックが薄いため省略可(Integration で十分)

10. ✅ **静的解析・ビルドを通して仕上げる**
    - `mise run api:ecs` / `api:phpstan` / `api:arkitect` / `api:test`(Step 4 で `tools/Arkitect/config.php` を更新済みなら arkitect が緑)
    - `mise run contract:format:check` / `contract:test` / `contract:compile:admin`
    - `cd src && bun --filter admin lint:check` / `style:check` / `build`
    - 手動動作確認: (a) `ReadAuditLog` あり管理ユーザーで `/audit-logs` 一覧が降順表示・絞り込み適用できる、(b) 詳細ページで snapshot が pretty JSON 表示、(c) 権限なしユーザーでサイドナビに項目が出ない & API が 403

## Decision Log

- 2026-05-05: ルートパスは `GET /admin/audit-logs/search`(一覧)と `GET /admin/audit-logs/{auditLogId}`(詳細)にする。理由: 既存の Song/Person/SongTag が `/search` を一覧の代名詞として使う構造になっており、これに合わせる方が読み手の認知負荷が低い
- 2026-05-05: ページネーションは `page` + `per_page` 方式に統一する(カーソル方式にしない)。理由: 既存の `Support\Domain\SearchCriteria\PerPage` enum と `Song`/`Person` の Search 系で確立したパターンを踏襲し、フロントの一覧 UI 共通化(ページネーション部品の再利用)を可能にする
- 2026-05-05: 絞り込み UI は適用ボタン押下時に URL クエリ更新 + 再 fetch する。理由: リアルタイム反映だと API 呼び出し過多になる、URL 共有性を持たせる、既存 SearchList のパターンを大きく崩さない
- 2026-05-05: `snapshot` は `target_type` 別に整形ビューを作らず、`<pre>` での JSON 整形表示に統一する。理由: 集約構造はリリース毎に変化し、target_type 別整形ビューは保守負債になる。監査用途では「その時点の生スナップショット」を確認できれば十分
- 2026-05-05: 監査ログ閲覧機構の置き場所は基盤と同じ `Support` 配下とする。理由: 監査ログは横断的関心事で、対象 (Person/Song/SongTag/AdminUser) 集約に紐づく Domain ロジックを持たない。読み取り側も同様に基盤として保つことで、対象パッケージへの逆流依存を回避できる
- 2026-05-05: `audit_logs` の Query 層は `Support\UseCase\AuditLog\Query\` 配下に置き、`SupportComponent::Application` の新設は行わない。理由: 既存 Song/Person の `Application/Query` 慣行とは少しずれるが、監査ログ機構は `Support` 配下の小さな機能群であり、それ単体のために arkitect コンポーネントを増やすコストの方が高い。`SupportComponent::UseCase` の既存 deps でそのまま動かせる。今後 Support 配下に Query が増えてきたタイミングで `SupportComponent::Application` を切り出すことを再検討する
- 2026-05-05: Eloquent モデル `App\Models\AuditLog` は前段の `20260505-audit-log.md` で導入済みのものをそのまま再利用する。`$casts = ['snapshot' => 'array']` により JSON ↔ 連想配列の変換が自動で行われるため、Query Service ではそのまま読み出せる
- 2026-05-05: snapshot のレスポンス型は OpenAPI で `object` (`additionalProperties: true`) として表現する。TypeSpec では `Record<unknown>` を使う。理由: snapshot 内部のキーは集約毎に異なり、固定スキーマ化はメンテ負債。閲覧 UI 側も生 JSON 表示しか行わないため型不要
- 2026-05-05: サイドナビは権限ベースで出し分けず、全管理ユーザーに「監査ログ」項目を表示する。アクセス制御は API 側の 403 で担保する。理由: 既存 Sidebar には権限ベース出し分け機構がなく、これを今回新設すると BFF middleware で JWT クレームを取り出して Astro.locals に流す等の改修が必要で、監査ログ機能のためだけにしては変更範囲が広い。権限ベース UI 出し分けは将来全体方針として整備する予定(今回の Scope 外)
- 2026-05-05: Controller / Presenter は既存の `App\Http\Controllers\Api\` / `App\Http\Presenters\Api\` 配下に揃える。Scope 文書中の「`Admin/AuditLog/`」記述は既存の慣行と異なるため、`Api/AuditLog/` を採用する
- 2026-05-05: 監査ログ閲覧 API は読み取りのみで `audit_logs` テーブル自身への監査ログ記録は行わない(前段 plan の Non-Scope を継承)。`SearchUseCase`/`GetUseCase` は `AuditLogRecorder` を一切使わない
- 2026-05-05: `Tests\TestCase::privilegedContext()` を 1 テストメソッド内で memoize するように修正。同メソッド内で複数回呼ばれた場合に毎回新しい UUID で `test@example.com` を insert しようとし unique 制約違反になる問題を解消(基盤導入時の既存実装の不具合を今回検出)。`SearchUseCase` のページング/絞り込みテストで `getInstance()` を複数回呼ぶ構造になったために顕在化
- 2026-05-05: レビュー指摘により `GetAuditLogTest` 新設、`paginatesResults` を 26 件 seed の本格検証に修正、`withGeneralAuth` を `WithAuth` trait に集約、SearchList の enum オプションを generated 派生に変更、DetailView の back 判定を `URLSearchParams.has` ベースに、デッドコードを除去
- 2026-05-05: 実行者の絞り込み・表示を UUID から名前ベースに変更。`admin_user_id` クエリパラメータと UI 表示を削除し、`admin_user_name` に置き換え。Query Service で `admin_users` JOIN し `name` を引く。`target_id` は UUID のまま(target は AdminUser/Person/Song/SongTag 混在で解決ロジックが target_type 依存になり保守負債のため)
- 2026-05-06: `admin_user_name` の LIKE は **前方一致**(`value%`)に変更。さらに LIKE メタ文字(`\`, `%`, `_`)を `escapeLike()` でエスケープ。理由: 中間一致は MySQL の B-tree インデックスを利用できず将来の拡張で遅くなる懸念がある、未エスケープのメタ文字を入力すると全件マッチや誤一致の原因になる

## Validation

- AC「`Permission::ReadAuditLog` が enum に追加され、`getName()` が `監査ログ閲覧` を返す」
  - `src/server/packages/AdminUser/Domain/Models/Permission.php` を直接読む。Unit テスト `tests/Unit/AdminUser/Domain/Models/PermissionTest.php` が既存ならテストケース追加、無ければ `mise run api:phpstan` で型整合のみ確認
- AC「`ReadAuditLog` を持たない管理ユーザーが `/admin/audit-logs/*` を叩くと 403」
  - Step 9 の Feature テスト `SearchAuditLogTest::test_returns_forbidden_when_admin_user_lacks_permission()` と `GetAuditLogTest::test_returns_forbidden_when_admin_user_lacks_permission()` で検証
- AC「`ReadAuditLog` を持つ管理ユーザーが Search を叩くと `created_at` 降順でページングされて返る」
  - Step 9 の Feature/Integration テストで `created_at` 降順、`page=2&per_page=N` のページング、`maxPage` の値を検証
- AC「`from`/`to` 範囲指定で `created_at` 範囲内のみ返る、各 criteria 指定で AND 絞り込み」
  - Step 9 Integration テスト `SearchUseCaseTest` で個別ケースを網羅
- AC「Get は 1 件返し、存在しないと 404」
  - Step 9 `GetUseCaseTest::test_returns_not_found_when_audit_log_does_not_exist()` と Feature テストで 200/404 を検証
- AC「管理画面 `/audit-logs` で一覧が表示され、絞り込みフォームが API クエリに反映」
  - 手動: 管理画面でフォーム入力 → 「適用」 → URL クエリと API リクエストが一致することを DevTools で確認
  - 自動: `cd src && bun --filter admin build` でビルドが通ることを最低限確認(既存に SolidJS 用テストハーネスが無ければ手動確認に留める)
- AC「`/audit-logs/{id}` で `<pre>` に整形 JSON が出る」
  - 手動: 詳細ページで `snapshot` が 2-space インデントで表示されることを DOM で確認
- AC「権限のないユーザーがクリックしても API が 403 を返してエラー表示になる」
  - 手動: 監査ログ権限を持たないテストユーザーでログインし、サイドナビ「監査ログ」をクリックして 403 由来のエラー表示が出ることを確認
- 静的解析・ビルド回帰
  - サーバ: `mise run api:ecs` / `mise run api:phpstan` / `mise run api:arkitect` / `mise run api:test`
  - 契約: `mise run contract:format:check` / `mise run contract:test` / `mise run contract:compile:admin`
  - 管理画面: `cd src && bun --filter admin lint:check` / `style:check` / `build`
