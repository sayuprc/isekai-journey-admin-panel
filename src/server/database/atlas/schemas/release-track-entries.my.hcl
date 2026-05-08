table "release_track_entries" {
  schema  = schema.db
  comment = "リリース収録曲"

  column "release_id" {
    null    = false
    type    = binary(16)
    comment = "リリースID"
  }
  column "song_id" {
    null    = false
    type    = binary(16)
    comment = "楽曲ID"
  }
  column "track_no" {
    null     = false
    type     = int
    unsigned = true
    comment  = "曲順"
  }

  primary_key {
    columns = [column.release_id, column.song_id]
  }

  index "fk_release_track_entries_song_id" {
    columns = [column.song_id]
  }

  index "release_track_entries_release_id_track_no_unique" {
    unique  = true
    columns = [column.release_id, column.track_no]
  }

  foreign_key "fk_release_track_entries_release_id" {
    columns     = [column.release_id]
    ref_columns = [table.releases.column.release_id]
    on_delete   = CASCADE
  }
  foreign_key "fk_release_track_entries_song_id" {
    columns     = [column.song_id]
    ref_columns = [table.songs.column.song_id]
    on_delete   = RESTRICT
  }
}
