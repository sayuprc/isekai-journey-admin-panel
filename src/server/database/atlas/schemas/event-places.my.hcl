table "event_places" {
  schema  = schema.db
  comment = "出来事場所関連"

  column "event_id" {
    null    = false
    type    = binary(16)
    comment = "出来事ID"
  }
  column "place_id" {
    null    = false
    type    = binary(16)
    comment = "場所ID"
  }

  primary_key {
    columns = [column.event_id, column.place_id]
  }

  index "fk_event_places_place_id" {
    columns = [column.place_id]
  }

  foreign_key "fk_event_places_event_id" {
    columns     = [column.event_id]
    ref_columns = [table.events.column.event_id]
    on_delete   = CASCADE
  }
  foreign_key "fk_event_places_place_id" {
    columns     = [column.place_id]
    ref_columns = [table.places.column.place_id]
    on_delete   = RESTRICT
  }
}
