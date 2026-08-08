# Execution Plan

## Title

楽曲一覧の楽曲種別・表示設定を GitHub ラベル風バッジで表示する

## Status

completed

## Background

楽曲一覧画面（管理画面 `/songs`）では、各楽曲の「楽曲種別」と「表示設定」を、テーブルのセル内にプレーンテキストとして表示している。

- 楽曲種別: `song.type.name` をそのまま `<td>` に出力（`src/admin/src/components/song/SearchList.tsx:326`）
- 表示設定: `song.isDisplay ? '表示する' : '表示しない'`（同 327 行目）

この表示はテキストのみで視覚的な強弱がなく、種別の違いや表示/非表示の状態を一覧でぱっと見分けにくい。同じ管理画面内では既に `EditableForm.tsx` / `CreateForm.tsx` のタグ選択 UI で daisyUI の `badge` クラスを使っており、状態を色付きラベルで表現する素地はある。GitHub の Issue ラベルのように背景色付きのバッジで提示することで、楽曲種別ごとの分類と表示状態を直感的に識別できるようにしたい。

## Goal

楽曲一覧画面のテーブル上で、楽曲種別と表示設定を背景色付きのラベル（バッジ）として表示し、状態の違いを一目で識別できるようにする。

## Scope

- `src/admin/src/components/song/SearchList.tsx`
  - 一覧テーブル中の「楽曲種別」セルをバッジ表示に変更
  - 「表示設定」セルをバッジ表示に変更（表示する / 表示しない で見た目を切り分ける）
- 上記で再利用するラベル/バッジ表現に関する小さなスタイル（daisyUI の `badge` クラス + Tailwind のカラーユーティリティ）の導入
- 既存のスタイル方針（daisyUI / Tailwind）を踏襲する

## Non-Scope

- 楽曲種別マスタ（`song-types`）への色情報カラムの追加・API 契約変更（`src/contracts`）
- サーバー側（`src/server`）のロジック変更
- 楽曲タグ（`song_tags`）の表示変更、楽曲詳細・編集画面（`pages/songs/[id].astro`、`EditableForm.tsx`、`CreateForm.tsx`）の表示変更
- 閲覧サイト（`src/viewer`）の変更
- 検索フォームの `select` UI の見た目変更
- 楽曲種別ごとの色をユーザーが編集できるようにする機能

## Acceptance Criteria

- 楽曲一覧の各行で、楽曲種別がテキストではなく背景色付きのバッジとして表示される
- 楽曲一覧の各行で、表示設定が「表示する」「表示しない」を区別できる背景色付きのバッジとして表示される
- 楽曲種別が複数ある場合、種別ごとに視覚的に区別できる（同一種別は同じ色、異なる種別は異なる色になる）
- 既存の検索・ソート・ページング・編集導線の挙動は変わらない
- `cd src && bun --filter admin lint:check` / `style:check` が通る

## Steps

✅ 1. `src/admin/src/components/song/SearchList.tsx` の冒頭（既存 import 群の直下、`PER_PAGE_OPTIONS` の上あたり）に、楽曲種別の `value: number` から daisyUI のバッジ色クラスを決定的に返す純関数 `songTypeBadgeClass(value: number): string` を定義する。
   - 候補色は daisyUI の意味色のうち、種別を識別する目的に適した中立的な色だけを使う配列にする: `['badge-primary', 'badge-secondary', 'badge-accent', 'badge-info']`
   - `value` を非負整数化したうえで `colors[value % colors.length]` を返す（`SongTypeValue` は現状 `1 | 2` だが将来増えうるため、any number に耐える式にする）
   - `success`/`warning`/`error` は表示設定側で意味付けしたいので種別側では使わない
✅ 2. 同ファイルに、表示設定（`isDisplay: boolean`）からバッジ色クラスを返す純関数 `isDisplayBadgeClass(isDisplay: boolean): string` を定義する。
   - `true` → `badge-success`（表示する＝肯定）
   - `false` → `badge-ghost`（表示しない＝控えめ・無効状態を示唆）
✅ 3. `<td>{song.type.name}</td>`（326 行目付近）を `<span class={`badge badge-sm ${songTypeBadgeClass(song.type.value)}`}>{song.type.name}</span>` を内包した `<td>` に置き換える。
✅ 4. `<td>{song.isDisplay ? '表示する' : '表示しない'}</td>`（327 行目付近）を `<span class={`badge badge-sm ${isDisplayBadgeClass(song.isDisplay)}`}>{song.isDisplay ? '表示する' : '表示しない'}</span>` を内包した `<td>` に置き換える。
✅ 5. 検索フォーム・ソート・ページング・編集ボタンの DOM/挙動には触れず、テーブル本体の 2 セルだけを変更したことを目視で確認する。
✅ 6. `cd src && bun --filter admin lint:check` を実行して lint エラーがないことを確認する。
✅ 7. `cd src && bun --filter admin style:check` を実行してフォーマット差分がないことを確認する。

## Decision Log

- 2026-05-03: 楽曲種別ごとの色マッピングは「マスタに色カラムを追加せず、フロント側で `song.type.value`（数値 ID）から決定的に色を引く」方針を採用。理由は Non-Scope にある通り `src/contracts` の API 契約変更を避けるためと、色は表示上の都合に過ぎず Source of Truth はマスタの存在そのものでよいため。`name` は将来変更されうるが `value` は ID として安定するので `value` を入力にする。
- 2026-05-03: 楽曲種別バッジは `badge-primary / badge-secondary / badge-accent / badge-info` の 4 色を `value % 4` で選ぶ。`success/warning/error` を含めないのは、それらが「表示する/表示しない」など状態系の意味を持つ色として温存しておきたいため（種別が状態色と被ると誤読を招く）。種別が 5 種以上に増えた場合は同色が再利用されるが、種別総数が少ないドメインのため許容する。
- 2026-05-03: 表示設定バッジは `表示する=badge-success`、`表示しない=badge-ghost` を採用。`badge-error` ではなく `badge-ghost` にしたのは、非表示はエラーではなく単に「無効化されている」状態であり、視覚的に弱める方が一覧の可読性に合うため。
- 2026-05-03: ヘルパー関数は共通モジュールには切り出さず `SearchList.tsx` 内のローカル定数・関数として閉じる。利用箇所が同ファイル内の 2 セルに限定されており、他コンポーネントで再利用する予定もないため、共通化のコストとシンプルさのバランスから「小さく保つ」を優先する。再利用が発生した時点で抽出する。
- 2026-05-04: バッジの彩度が強すぎたため `badge-soft` 修飾子を追加して輝度を下げた。種別バッジは全色に `badge-soft` を、表示設定バッジは `badge-success` のみに `badge-soft` を付与（`badge-ghost` は元から無彩色なので不要）。

## Validation

- AC「楽曲種別がバッジで表示される」: `cd src && bun --filter admin dev` で `/songs` を開き、楽曲種別セルが `badge` クラス付き `<span>` で背景色付きに描画されていることを DevTools と目視で確認する。
- AC「表示設定がバッジで表示される」: 同上画面で「表示する」が success 色、「表示しない」が ghost 色のバッジで描画されていることを確認する。検索フィルタで `is_display=true` / `false` を切り替えてどちらの見た目も確認する。
- AC「種別ごとに色が区別できる」: 楽曲種別が 2 種以上存在するデータで一覧を表示し、`value` が異なる種別が異なる色のバッジで描画され、同じ種別は同じ色になることを確認する。
- AC「既存挙動が変わらない」: 検索（楽曲名・楽曲種別・表示設定）、ソート（表示順/楽曲名 × 昇/降順）、ページング、編集ボタン遷移、リセットがいずれも従来どおり動くことを手動で一通り操作して確認する。
- AC「lint/style が通る」: `cd src && bun --filter admin lint:check && bun --filter admin style:check` がすべて成功することを確認する。