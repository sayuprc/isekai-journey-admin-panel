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
  column "title_lower" {
    null = true
    type = varchar(255)
    as {
      expr = "lower(`title`)"
      type = VIRTUAL
    }
    comment = "楽曲名（小文字）"
  }
  column "description" {
    null    = false
    type    = text
    comment = "説明"
  }
  column "type" {
    null     = false
    type     = tinyint
    unsigned = true
    comment  = "種別"
  }
  column "attribute" {
    null     = true
    type     = tinyint
    unsigned = true
    comment  = "属性"
  }
  column "is_display" {
    null    = false
    type    = bool
    default = true
    comment = "表示するか"
  }
  column "order_no" {
    null     = false
    type     = int
    unsigned = true
    comment  = "表示順"
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

  index "idx_songs_title_lower" {
    columns = [column.title_lower]
  }
}
