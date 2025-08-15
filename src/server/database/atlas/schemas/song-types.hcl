table "song_types" {
  schema = schema.main
  column "song_type_id" {
    null = false
    type = blob
  }
  column "song_type_name" {
    null = false
    type = text
  }
  column "created_at" {
    null = false
    type = datetime
  }
  column "updated_at" {
    null = false
    type = datetime
  }

  primary_key {
    columns = [column.song_type_id]
  }
}
