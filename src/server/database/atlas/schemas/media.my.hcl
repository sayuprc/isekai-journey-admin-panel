table "media" {
  schema  = schema.db
  comment = "メディア"

  column "media_id" {
    null    = false
    type    = binary(16)
    comment = "メディアID"
  }
  column "title" {
    null    = false
    type    = varchar(255)
    comment = "タイトル"
  }
  column "url" {
    null    = false
    type    = text
    comment = "URL"
  }
  column "type" {
    null     = false
    type     = tinyint
    unsigned = true
    comment  = "種別"
  }
  column "is_display" {
    null    = false
    type    = bool
    comment = "表示するか"
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
    columns = [column.media_id]
  }
}
