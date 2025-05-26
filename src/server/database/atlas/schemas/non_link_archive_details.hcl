table "non_link_archive_details" {
  schema = schema.public
  column "archive_id" {
    null = false
    type = uuid
  }
  column "archived_on" {
    null = false
    type = date
  }
  column "order_no" {
    null = false
    type = integer
  }
  primary_key {
    columns = [column.archive_id]
  }
  foreign_key "non_link_archive_details_archive_id_fkey" {
    columns     = [column.archive_id]
    ref_columns = [table.archives.column.archive_id]
    on_update   = NO_ACTION
    on_delete   = CASCADE
  }
}
