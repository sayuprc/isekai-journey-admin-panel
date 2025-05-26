table "song_creators" {
  schema = schema.public
  column "song_id" {
    null = false
    type = uuid
  }
  column "creator_id" {
    null = false
    type = uuid
  }
  column "role" {
    null = false
    type = integer
  }
  primary_key {
    columns = [
      column.song_id,
      column.creator_id,
      column.role,
    ]
  }
  foreign_key "song_creators_song_id_fkey" {
    columns     = [column.song_id]
    ref_columns = [table.songs.column.song_id]
    on_update   = NO_ACTION
    on_delete   = CASCADE
  }
  foreign_key "song_creators_creator_id_fkey" {
    columns     = [column.creator_id]
    ref_columns = [table.creators.column.creator_id]
    on_update   = NO_ACTION
    on_delete   = CASCADE
  }
}
