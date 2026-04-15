table "performers" {
  schema  = schema.db
  comment = "共演者"

  column "performer_id" {
    null    = false
    type    = binary(16)
    comment = "共演者ID"
  }
  column "name" {
    null    = false
    type    = varchar(255)
    comment = "共演者名"
  }
  column "name_lower" {
    null = true
    type = varchar(255)
    as {
      expr = "lower(`name`)"
      type = VIRTUAL
    }
    comment = "共演者名（小文字）"
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
    columns = [column.performer_id]
  }

  index "idx_performers_name_lower" {
    columns = [column.name_lower]
  }

  index "idx_performers_name_unique" {
    unique  = true
    columns = [column.name]
  }
}
