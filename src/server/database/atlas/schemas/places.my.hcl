table "places" {
  schema  = schema.db
  comment = "場所"

  column "place_id" {
    null    = false
    type    = binary(16)
    comment = "場所ID"
  }
  column "name" {
    null    = false
    type    = varchar(255)
    comment = "場所名"
  }
  column "name_lower" {
    null = true
    type = varchar(255)
    as {
      expr = "lower(`name`)"
      type = VIRTUAL
    }
    comment = "場所名(小文字)"
  }
  column "kind" {
    null     = false
    type     = tinyint
    unsigned = true
    comment  = "場所区分"
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
    columns = [column.place_id]
  }

  index "idx_places_name_lower" {
    columns = [column.name_lower]
  }

  index "places_name_unique" {
    unique  = true
    columns = [column.name]
  }
}
