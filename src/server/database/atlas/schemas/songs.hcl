table "songs" {
  schema = schema.public
  column "song_id" {
    null = false
    type = uuid
  }
  column "title" {
    null = false
    type = text
  }
  column "description" {
    null = false
    type = text
  }
  column "song_type_id" {
    null = false
    type = uuid
  }
  column "order_no" {
    null = false
    type = integer
  }
  primary_key {
    columns = [column.song_id]
  }
}
