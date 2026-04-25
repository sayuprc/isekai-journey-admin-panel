# GitHub Copilot Instructions

このファイルは GitHub Copilot 向けの薄い入口です。共通のリポジトリ地図、変更ルート、文書配置、共有ルールは `AGENTS.md` を参照してください。

## まず見る入口

- `AGENTS.md`: 共通の地図と変更の入口
- `README.md`: セットアップと主要コマンド
- `ARCHITECTURE.md`: Source of Truth と変更ルート

## Copilot 向けの追加入口

- パス別の詳細ルールは `.github/instructions/` の `*.instructions.md` を使う
- 文書更新時は `.github/instructions/04-docs.instructions.md` に従い、入口文書は短く、詳細は下位文書へ分ける

このファイルに共通ルールを複写せず、必要な内容は `AGENTS.md` か該当の instructions に集約します。
