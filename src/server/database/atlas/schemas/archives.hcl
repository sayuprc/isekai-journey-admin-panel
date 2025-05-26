table "archives" {
  schema = schema.public
  column "archive_id" {
    null = false
    type = uuid
  }
  column "song_id" {
    null = false
    type = uuid
  }
  column "archive_type" {
    null = false
    type = integer
  }
  primary_key {
    columns = [column.archive_id]
  }
  foreign_key "archives_song_id_fkey" {
    columns     = [column.song_id]
    ref_columns = [table.songs.column.song_id]
    on_update   = NO_ACTION
    on_delete   = CASCADE
  }
}
