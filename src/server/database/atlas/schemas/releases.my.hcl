table "releases" {
  schema  = schema.db
  comment = "リリース"

  column "release_id" {
    null    = false
    type    = binary(16)
    comment = "リリースID"
  }
  column "title" {
    null    = false
    type    = varchar(255)
    comment = "タイトル"
  }
  column "type" {
    null     = false
    type     = tinyint
    unsigned = true
    comment  = "種別"
  }
  column "distribution_type" {
    null     = false
    type     = tinyint
    unsigned = true
    comment  = "流通形態"
  }
  column "released_on" {
    null    = false
    type    = date
    comment = "発売日"
  }
  column "description" {
    null    = false
    type    = text
    comment = "説明"
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
    columns = [column.release_id]
  }
}
