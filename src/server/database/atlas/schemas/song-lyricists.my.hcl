table "song_lyricists" {
  schema  = schema.db
  comment = "楽曲作詞者"

  column "song_id" {
    null    = false
    type    = binary(16)
    comment = "楽曲ID"
  }
  column "creator_id" {
    null    = false
    type    = binary(16)
    comment = "作詞者ID"
  }
  column "order_no" {
    null    = false
    type    = tinyint
    comment = "表示順"
  }

  primary_key {
    columns = [column.song_id, column.creator_id]
  }

  index "fk_song_lyricists_creator_id" {
    columns = [column.creator_id]
  }

  foreign_key "fk_song_lyricists_song_id" {
    columns     = [column.song_id]
    ref_columns = [table.songs.column.song_id]
    on_delete   = CASCADE
  }
  foreign_key "fk_song_lyricists_creator_id" {
    columns     = [column.creator_id]
    ref_columns = [table.creators.column.creator_id]
    on_delete   = RESTRICT
  }
}
