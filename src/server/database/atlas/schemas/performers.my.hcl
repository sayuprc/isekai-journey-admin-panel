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
}
