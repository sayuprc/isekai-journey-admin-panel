# Title

ジャケット URL カラムを代表色カラムへ置き換える

## Status

completed

## Background

ジャケットアートを法的な理由で廃止することが決まった。自前ホストの継続が難しく、
公式画像のホットリンクは `srcset` もキャッシュも相手任せになり LCP を制御できないため、画像そのものを viewer から無くす
判断の背景は [ADR-0015](../../adr/ADR-0015-do-not-host-rights-holder-images.md) に残した

一方でリリース一覧・ホーム・楽曲タイルはジャケットを視覚的な識別子として使っている
代替として、リリースごとの代表色を 1 色持つ方針にした

この計画は 5 本に分けた作業の 1 本目で、後続 4 本すべてがこのカラムに依存する
カラムは追加ではなく `jacket_art_url` からの置き換えにするため、**この PR の時点で viewer からジャケットが消える**
一覧やホームの見た目が整うのは 2 本目以降で、その間の中途半端な状態は許容する

## Goal

`releases.jacket_art_url` を代表色カラム `color` へ置き換え、コントラクト・サーバー・admin を色ベースに切り替える
viewer は型が通り画面が壊れない最小限の対応に留め、見た目の作り直しは後続に委ねる

## Scope

- `src/server/database/atlas/schemas/releases.my.hcl` の `jacket_art_url` を `color` へ置き換える
- `src/contracts` の viewer / admin から `jacketArtUrl` を削除し `color` を追加する
- `src/server/packages/Release` と HTTP 各層を `color` に差し替える
- `src/admin` のアップロード経路を削除し、hex 入力に置き換える
- `src/viewer` のジャケット参照を落とす(表示の作り直しはしない)

## Non-Scope

- リリース一覧・ホーム・詳細の見た目(2〜4 本目で行う)
- ストレージの実ファイル削除(5 本目で行う)
- ジャケットからの色抽出機能(将来 admin に作る)

## Acceptance Criteria

- `releases` に `color`(`varchar(7)`, NOT NULL)があり、`jacket_art_url` が存在しない
- 全リリースの `color` が固定値 `#989899` になっている
- viewer / admin / server の生成コードから `jacketArtUrl` が消え、`color` が現れる
- admin のリリース編集画面から hex を保存でき、再読込後も保持される
- viewer の全ページが画像なしで表示でき、型検査と lint が通る
- リリース詳細と楽曲詳細の `og:image` が既定画像になっている

## Steps

- [x] `releases.my.hcl` の `jacket_art_url` を `color`(`varchar(7)`, NOT NULL, コメント `代表色`)へ書き換える
- [x] `mise run migrate:dry-run` で Atlas の出力を確認する。DROP + ADD だったため `color` に DEFAULT `#989899` を持たせた
- [x] `src/contracts` の viewer / admin から `jacketArtUrl` scalar とモデル・リクエスト定義を削除し、
      `color` scalar(`^#[0-9a-f]{6}$`)を必須項目として追加する
      アップロード API(`uploadJacketArt`)と `ReleaseJacketArtUploadResponse` も削除した
- [x] `mise run contract:re-compile` と `api:generate` / `viewer:generate` / `admin:generate` を実行する
- [x] `src/server/packages/Release` に `Color` 値オブジェクトを追加し、`Release`・Factory・Repository・HTTP 各層を差し替える
      (`JacketArtUrl` をリネームして流用した)
- [x] `src/admin` のアップロード経路(`server/routes/releases.ts`、`utils/client.ts`、関連テスト)を削除し、
      リリース編集フォームを hex 入力に置き換える
- [ ] `src/viewer` から `JacketArt` の呼び出しと `representativeJacketArtUrl` を落とし、
      OGP を既定画像に変える。`SongTile` は既存のアイコンフォールバックに任せる
- [x] テストで `color` の往復(保存 → 取得)を検証する(既存の Feature / Integration テストを色に更新)

## Release

Atlas はこの変更を rename ではなく DROP + ADD として計画する
`jacket_art_url` はカラムごと落ちるため、事前に固定値へ UPDATE しても引き継がれない

デプロイのマイグレーションは次の 1 文になる

```sql
ALTER TABLE `releases`
  DROP COLUMN `jacket_art_url`,
  ADD COLUMN `color` varchar(7) NOT NULL COMMENT "代表色" AFTER `description`;
```

DEFAULT を持たせないため、既存行の `color` は空文字になる(strict mode でも ALTER 自体は通る)
マイグレーション直後に手動で埋める

```sql
UPDATE `releases` SET `color` = '#989899' WHERE `color` = '';
```

## Decision Log

この計画一式に共通する判断をここに集約する。2〜5 本目は差分だけを自分の Decision Log に書く

- 2026-08-09: ジャケットアートは viewer・コントラクト・DB カラム・ストレージの実ファイルまで全廃する
  1 箇所でも配信が残ると廃止の理由が解消しないため
- 2026-08-09: 代替表現はリリースごとの代表色にする。単色に著作物性はなく、抽出も著作権法 30 条の 4 の情報解析にあたる
- 2026-08-09: 色は `releases`(版)に持ち、グループの代表色は `representativeJacketArtUrl` と同じ規則で導出する
  抽出元が版のジャケットであるため。版が複数あるグループは 85 件中 4 件
- 2026-08-09: カラムは追加せず `jacket_art_url` を `color` へ置き換える
  併存期間を作らないぶん作業が単純になり、その間に viewer の見た目が壊れることは許容する
- 2026-08-09: 本番は「手動で固定値へ UPDATE → デプロイのマイグレーションで rename」を想定していたが、
  Atlas が rename を検出せず DROP + ADD を計画するため取りやめた
- 2026-08-09: `color` に DEFAULT は持たせない。既存行は空文字になるので、マイグレーション直後に手動で UPDATE する
- 2026-08-09: 差分が大きくなりすぎたため、この計画をさらに細かい PR に割る
  契約 + スキーマ (#988) / サーバー (#989) / admin / viewer の 4 本に分け、順に PR を出す
  途中の PR で CI が落ちるのは許容する
- 2026-08-09: admin の色入力はカラーピッカーと hex テキストの 2 つを同じ値に束ねる
  ピッカーだけだと既存の hex を貼れず、テキストだけだと色が見えないため
- 2026-08-09: 移行時の色は固定値 `#989899` を入れる。実データは後日入れ直す
  ローカルの MinIO には 88 件中 87 件のジャケットが残っているが、抽出は今回のスコープに含めない
- 2026-08-09: 色は NOT NULL にする。事前にデータを入れる前提のため、未設定状態を表現する必要がない
- 2026-08-09: 将来の抽出機能は「アップロード → 抽出 → 候補から選択(デフォルト Vibrant)→ 画像は破棄」を理想形とし、
  Node + node-vibrant で実装する。今回は admin の hex 手入力のみ
- 2026-08-09: 上記の抽出方針を改め、ブラウザ内(`node-vibrant/browser`)で抽出する
  画像を BFF/Laravel に送らず、選んだ hex だけを既存 create/update で保存する。詳細は `20260809-admin-extract-release-color.md`
- 2026-08-09: 作業は `feature/remove-jacket-art` 配下に 5 本の PR で分け、すべて揃ってから 1 回で `dev` に落とす

## Validation

- PR 群 (#988〜#1001) として `feature/remove-jacket-art` にマージ済み
- スキーマ・コントラクト・admin/viewer/server の置換を確認済み
