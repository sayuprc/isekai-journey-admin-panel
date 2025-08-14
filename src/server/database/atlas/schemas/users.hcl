table "users" {
  schema = schema.main
  column "user_id" {
    null = false
    type = blob
  }
  column "email" {
    null = false
    type = text
  }
  column "password" {
    null = false
    type = text
  }
  column "created_at" {
    null = false
    type = datetime
  }
  column "updated_at" {
    null = false
    type = datetime
  }

  primary_key {
    columns = [column.user_id]
  }

  index "users_email_unique" {
    unique = true
    columns = [column.email]
  }
}
