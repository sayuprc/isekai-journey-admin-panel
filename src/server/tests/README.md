# テスト戦略

3 つの階層でテストをする

1. Unit
2. Integration
3. Feature

## 環境変数

- `src/server/.env.testing` にはテスト時に必要かつ不変な値 (主にテスト用 DB 接続) だけを置く
- `APP_KEY` / JWT / pepper / Passkey RP など変化しうる値はソースに固定せず、対象テストで都度設定する
- 起動に必要な `APP_KEY` のみ、未設定時に `Tests\TestCase` が実行時生成する
- `phpunit.xml` にはドライバー切替などプロセス全体で固定する値を置く

## Unit

### テスト対象

- Domain 層
- UseCase 層

### 内容

依存関係は Mock を使う  
想定するケースをできるだけテストする

## Integration

### テスト対象

- Domain 層
- UseCase 層
- Infrastructure 層

※I/O(DB や外部 API など)があるもののみを対象とする

### 内容

依存関係はできるだけ本物を使う  
基本的にハッピーパスのみをテストする

## Feature

### テスト対象

- Infrastructure 層

### 内容

依存関係はできるだけ本物を使う  
ユーザーの挙動に沿ったケースをテストする
