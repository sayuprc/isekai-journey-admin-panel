table "events" {
  schema  = schema.db
  comment = "出来事"

  column "event_id" {
    null    = false
    type    = binary(16)
    comment = "出来事ID"
  }
  column "title" {
    null    = false
    type    = varchar(255)
    comment = "タイトル"
  }
  column "title_lower" {
    null = true
    type = varchar(255)
    as {
      expr = "lower(`title`)"
      type = VIRTUAL
    }
    comment = "タイトル(小文字)"
  }
  column "type" {
    null     = false
    type     = tinyint
    unsigned = true
    comment  = "出来事種別"
  }
  column "started_at" {
    null    = false
    type    = datetime
    comment = "開始日時"
  }
  column "ended_at" {
    null    = false
    type    = datetime
    comment = "終了日時"
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
    columns = [column.event_id]
  }

  index "idx_events_title_lower" {
    columns = [column.title_lower]
  }

  index "idx_events_started_at" {
    columns = [column.started_at]
  }
}
