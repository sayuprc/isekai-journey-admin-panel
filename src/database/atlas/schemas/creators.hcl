table "creators" {
  schema = schema.public
  column "creator_id" {
    null = false
    type = uuid
  }
  column "creator_name" {
    null = false
    type = text
  }
  primary_key {
    columns = [column.creator_id]
  }
}
