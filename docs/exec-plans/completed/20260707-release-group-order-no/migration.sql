-- release_groups.order_no 追加のデータ移行
-- 実行順序: `mise run migrate:local` で order_no (default 1) を追加した後に流す

-- ============================================================
-- 手順 1: `mise run migrate:local` を実行
-- (release_groups.order_no が atlas により追加される)
-- ============================================================

-- ============================================================
-- 手順 2: 現在の表示順 (最古発売日の降順、同日はタイトル昇順) で
-- order_no を 10 刻み (10, 20, 30, ...) でバックフィルする
-- 間に挿入したいときに全体を振り直さなくて済むように隙間を空けておく
-- リリース未登録のグループは first_released_on が NULL のため末尾に並ぶ
-- ============================================================

UPDATE `release_groups` `rg`
INNER JOIN (
  SELECT
    `release_group_id`,
    ROW_NUMBER() OVER (ORDER BY `first_released_on` DESC, `title` ASC) * 10 AS `rn`
  FROM (
    SELECT
      `rg`.`release_group_id`,
      `rg`.`title`,
      MIN(`r`.`released_on`) AS `first_released_on`
    FROM `release_groups` `rg`
    LEFT JOIN `releases` `r` ON `r`.`release_group_id` = `rg`.`release_group_id`
    GROUP BY `rg`.`release_group_id`, `rg`.`title`
  ) `grouped`
) `ranked` ON `ranked`.`release_group_id` = `rg`.`release_group_id`
SET `rg`.`order_no` = `ranked`.`rn`;
