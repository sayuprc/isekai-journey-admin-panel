table "event_urls" {
  schema  = schema.db
  comment = "出来事URL"

  column "event_id" {
    null    = false
    type    = binary(16)
    comment = "出来事ID"
  }
  column "order_no" {
    null     = false
    type     = int
    unsigned = true
    comment  = "表示順"
  }
  column "label" {
    null    = true
    type    = varchar(255)
    comment = "表示名"
  }
  // URL は日本語ドメインやパスをそのまま保持できるよう text のままにする
  column "url" {
    null    = false
    type    = text
    comment = "URL"
  }

  primary_key {
    columns = [column.event_id, column.order_no]
  }

  foreign_key "fk_event_urls_event_id" {
    columns     = [column.event_id]
    ref_columns = [table.events.column.event_id]
    on_delete   = CASCADE
  }
}
