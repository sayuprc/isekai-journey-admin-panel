---
id: ADR-0011
status: accepted
superseded_by: null
applies_to: [api]
---

# API の ORM を Eloquent から emonkak/orm へ移行する

## Context

API サーバーは ADOP アーキテクチャを採用しており([ADR-0006](./ADR-0006-use-adop-architecture-for-server.md))、Repository / QueryService は Others 層の実装である。これらは Eloquent を使ってきたが、次の問題があった

- **静的解析との相性が悪い**：`Model::query()` のマジックメソッド、動的プロパティ、リレーションは PHPStan で型を追えず、`@phpstan-ignore` を撒く必要があった(例: Viewer の `SongQueryService`)
- **責務が曖昧**：ActiveRecord は「テーブル行」と「ドメインの永続化」を同じモデルに混在させ、Others 層に閉じるべき永続化詳細が `app/Models` に漏れていた

SQL ビルダ + Fetcher 型の `emonkak/orm` は導入済みだったが、配線が実 DB と噛み合わず動作しない状態だった(接続コネクタが SQLite のみ・実行時 DB は MySQL / 本番 TiDB、emonkak を使う実装がゼロ、トランザクションが Laravel 接続と分断)

## Decision

API の ORM を Eloquent から `emonkak/orm` へ移行し、`app/Models` の Eloquent モデルを撤去する。変更は Others 層(Repository / QueryService)に閉じ、Domain / Application は変更しない

実現方針：

- **接続・トランザクションは Laravel の Connection を共有する。** `emonkak` の `PDOInterface` には `new PDOAdapter(DB::connection()->getPdo())` をバインドし、接続設定は `config/database.php` に一元化する。Eloquent(ActiveRecord 層)は撤去するが、`Illuminate\Database` の接続・トランザクション層は引き続き利用する。`DB::transaction`(`DbTransaction`)はそのまま emonkak のクエリを包む。専用コネクタ(`SQLiteConnector` など)は作らず削除する
- **文法は `DefaultGrammar`** を用いる。識別子をバッククォートで囲むため MySQL / TiDB と互換
- **行→ドメインの復元は明示的な `Entity::reconstruct(...)` に統一する。** `ArrayFetcher` で行を配列取得し、Repository 内で明示的に再構築する。valinor ベースの `Mapper` は廃止する(実行時 reflection で静的解析と相性が悪く、移行の主動機に反するため)
- **リレーション/集約のネスト取得は明示的なマルチクエリで行う。** 親を取得して識別子を集め、子を `WHERE ... IN (...)` で一括取得し、PHP 側で親ごとに group して組み立てる(子は 1 クエリなので N+1 にならない)。当初は emonkak の `RelationFetcher` を採用する想定だったが、`with()` が期待する `RelationInterface` と `Relations::oneToMany()` が返す `Relation` のジェネリクスが食い違い、`ArrayFetcher` を不変テンプレート引数へ渡す箇所も covariance 誤検知となるため、PHPStan level 10 では `@phpstan-ignore` 無しに通せない。本移行の主目的(静的解析の健全性)と矛盾するため、RelationFetcher は使わない
- **段階移行**：共通足場(PDO 共有の再配線 + クエリ生成のヘルパ)を整えたのち、簡単なパッケージでパターンを確定 → Song(リレーション / upsert / カーソルページング)で難所を検証 → 残りを順に移行し、最後に `app/Models` を撤去する。各段階でテストを緑に保つ

## Consequences

### Positive

- Repository / QueryService が素の SQL ビルダになり、PHPStan が全経路を追える。`@phpstan-ignore` を除去できる
- 永続化の詳細が Others 層に閉じ、`app/Models` が消えることで ADOP の層境界が明確になる
- 接続を Laravel と共有するため、テストの `DatabaseTransactions` が emonkak の書き込みもロールバックでき、テスト基盤を作り直さずに済む
- 接続設定(MySQL / TiDB / SSL / テスト DB 切替)は Laravel 側の単一の設定に保てる

### Negative

- Eloquent の糖衣(自動 timestamps、`upsert` の `ON DUPLICATE KEY UPDATE`、`with()` eager load、Collection)は手作業で置き換える必要がある
- ネスト VO の組み立てを明示的に書くため、Repository のコード量は増える
- `Illuminate\Database`(接続・トランザクション層のみ)への依存は残る。Eloquent は撤去するが Laravel の DB 層を完全には捨てない、という非自明な切り分けになる
- `App\Models` を直接 import しているテスト(シード / アサート)は書き換えが必要
