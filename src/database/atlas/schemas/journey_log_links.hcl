table "journey_log_links" {
  schema = schema.public
  column "journey_log_link_id" {
    null = false
    type = uuid
  }
  column "journey_log_id" {
    null = false
    type = uuid
  }
  column "journey_log_link_type_id" {
    null = false
    type = uuid
  }
  column "journey_log_link_name" {
    null = false
    type = text
  }
  column "url" {
    null = false
    type = text
  }
  column "order_no" {
    null = false
    type = integer
  }
  primary_key {
    columns = [column.journey_log_link_id]
  }
  foreign_key "journey_log_links_journey_log_id_fkey" {
    columns     = [column.journey_log_id]
    ref_columns = [table.journey_logs.column.journey_log_id]
    on_update   = NO_ACTION
    on_delete   = CASCADE
  }
  foreign_key "journey_log_links_journey_log_link_type_id_fkey" {
    columns     = [column.journey_log_link_type_id]
    ref_columns = [table.journey_log_link_types.column.journey_log_link_type_id]
    on_update   = NO_ACTION
    on_delete   = NO_ACTION
  }
}
