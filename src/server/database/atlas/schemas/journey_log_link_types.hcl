table "journey_log_link_types" {
  schema = schema.public
  column "journey_log_link_type_id" {
    null = false
    type = uuid
  }
  column "journey_log_link_type_name" {
    null = false
    type = text
  }
  column "order_no" {
    null = false
    type = integer
  }
  primary_key {
    columns = [column.journey_log_link_type_id]
  }
}
