table "creators" {
  schema  = schema.db
  comment = "クリエイター"

  column "creator_id" {
    null    = false
    type    = binary(16)
    comment = "クリエイターID"
  }
  column "name" {
    null    = false
    type    = varchar(255)
    comment = "クリエイター名"
  }
  column "order_no" {
    null    = false
    type    = tinyint
    comment = "表示順"
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
    columns = [column.creator_id]
  }
}
