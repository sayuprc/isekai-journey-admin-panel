-- Release format 階層引き上げのデータ移行
-- 実行順序が重要: 手順 1 を `mise run migrate:local` より先に local DB へ流す
-- (atlas apply は release_media.format を削除するため、先にバックフィルしないと情報が失われる)

-- ============================================================
-- 手順 1: release_formats を先行作成してバックフィル (DDL 適用前)
-- ============================================================

CREATE TABLE IF NOT EXISTS `release_formats` (
  `release_id` binary(16) NOT NULL COMMENT 'リリースID',
  `format` tinyint unsigned NOT NULL COMMENT '提供形態',
  PRIMARY KEY (`release_id`, `format`),
  CONSTRAINT `fk_release_formats_release_id` FOREIGN KEY (`release_id`) REFERENCES `releases` (`release_id`) ON DELETE CASCADE
) COMMENT = 'リリース提供形態';

INSERT INTO `release_formats` (`release_id`, `format`)
SELECT DISTINCT `release_id`, `format`
FROM `release_media`;

-- ============================================================
-- 手順 2: `mise run migrate:local` を実行
-- (release_media.format 削除 / name 追加が atlas により適用される)
-- ============================================================

-- ============================================================
-- 手順 3: 「CD 盤 / 配信盤」の統合候補を列挙 (統合は admin で手動)
-- 同一 group 内で released_on が同じ Release のペアが対象
-- ============================================================

SELECT
  BIN_TO_UUID(rg.release_group_id) AS release_group_id,
  rg.title AS group_title,
  BIN_TO_UUID(r.release_id) AS release_id,
  r.name AS release_name,
  r.released_on,
  GROUP_CONCAT(rf.format ORDER BY rf.format) AS formats
FROM releases r
INNER JOIN release_groups rg ON rg.release_group_id = r.release_group_id
LEFT JOIN release_formats rf ON rf.release_id = r.release_id
WHERE EXISTS (
  SELECT 1
  FROM releases other
  WHERE other.release_group_id = r.release_group_id
    AND other.released_on = r.released_on
    AND other.release_id <> r.release_id
)
GROUP BY r.release_id, rg.release_group_id, rg.title, r.name, r.released_on
ORDER BY rg.title, r.released_on, r.name;
