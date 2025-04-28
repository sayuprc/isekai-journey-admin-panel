table "journey_logs" {
  schema = schema.public
  column "journey_log_id" {
    null = false
    type = uuid
  }
  column "story" {
    null = false
    type = text
  }
  column "from_on" {
    null = false
    type = date
  }
  column "to_on" {
    null = false
    type = date
  }
  column "order_no" {
    null = false
    type = integer
  }
  primary_key {
    columns = [column.journey_log_id]
  }
}
