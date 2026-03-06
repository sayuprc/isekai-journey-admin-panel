table "songs" {
  schema  = schema.db
  comment = "楽曲"

  column "song_id" {
    null    = false
    type    = binary(16)
    comment = "楽曲ID"
  }
  column "title" {
    null    = false
    type    = varchar(255)
    comment = "楽曲名"
  }
  column "description" {
    null    = false
    type    = text
    comment = "説明"
  }
  column "type" {
    null    = false
    type    = tinyint
    comment = "種別"
  }
  column "attribute" {
    null    = true
    type    = tinyint
    comment = "属性"
  }
  column "created_at" {
    null    = false
    type    = datetime
    comment = "作成日時"
  }
  column "updated_at" {
    null    = false
    type    = datetime
    comment = "更新日時"
  }

  primary_key {
    columns = [column.song_id]
  }
}
