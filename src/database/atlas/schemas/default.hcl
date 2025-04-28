table "cache" {
  schema = schema.public
  column "key" {
    null = false
    type = character_varying(255)
  }
  column "value" {
    null = false
    type = text
  }
  column "expiration" {
    null = false
    type = integer
  }
  primary_key {
    columns = [column.key]
  }
}
table "cache_locks" {
  schema = schema.public
  column "key" {
    null = false
    type = character_varying(255)
  }
  column "owner" {
    null = false
    type = character_varying(255)
  }
  column "expiration" {
    null = false
    type = integer
  }
  primary_key {
    columns = [column.key]
  }
}
table "failed_jobs" {
  schema = schema.public
  column "id" {
    null = false
    type = bigserial
  }
  column "uuid" {
    null = false
    type = character_varying(255)
  }
  column "connection" {
    null = false
    type = text
  }
  column "queue" {
    null = false
    type = text
  }
  column "payload" {
    null = false
    type = text
  }
  column "exception" {
    null = false
    type = text
  }
  column "failed_at" {
    null    = false
    type    = timestamp(0)
    default = sql("CURRENT_TIMESTAMP")
  }
  primary_key {
    columns = [column.id]
  }
  unique "failed_jobs_uuid_unique" {
    columns = [column.uuid]
  }
}
table "job_batches" {
  schema = schema.public
  column "id" {
    null = false
    type = character_varying(255)
  }
  column "name" {
    null = false
    type = character_varying(255)
  }
  column "total_jobs" {
    null = false
    type = integer
  }
  column "pending_jobs" {
    null = false
    type = integer
  }
  column "failed_jobs" {
    null = false
    type = integer
  }
  column "failed_job_ids" {
    null = false
    type = text
  }
  column "options" {
    null = true
    type = text
  }
  column "cancelled_at" {
    null = true
    type = integer
  }
  column "created_at" {
    null = false
    type = integer
  }
  column "finished_at" {
    null = true
    type = integer
  }
  primary_key {
    columns = [column.id]
  }
}
table "jobs" {
  schema = schema.public
  column "id" {
    null = false
    type = bigserial
  }
  column "queue" {
    null = false
    type = character_varying(255)
  }
  column "payload" {
    null = false
    type = text
  }
  column "attempts" {
    null = false
    type = smallint
  }
  column "reserved_at" {
    null = true
    type = integer
  }
  column "available_at" {
    null = false
    type = integer
  }
  column "created_at" {
    null = false
    type = integer
  }
  primary_key {
    columns = [column.id]
  }
  index "jobs_queue_index" {
    columns = [column.queue]
  }
}
table "migrations" {
  schema = schema.public
  column "id" {
    null = false
    type = serial
  }
  column "migration" {
    null = false
    type = character_varying(255)
  }
  column "batch" {
    null = false
    type = integer
  }
  primary_key {
    columns = [column.id]
  }
}
table "personal_access_tokens" {
  schema = schema.public
  column "id" {
    null = false
    type = bigserial
  }
  column "tokenable_type" {
    null = false
    type = character_varying(255)
  }
  column "tokenable_id" {
    null = false
    type = bigint
  }
  column "name" {
    null = false
    type = character_varying(255)
  }
  column "token" {
    null = false
    type = character_varying(64)
  }
  column "abilities" {
    null = true
    type = text
  }
  column "last_used_at" {
    null = true
    type = timestamp(0)
  }
  column "expires_at" {
    null = true
    type = timestamp(0)
  }
  column "created_at" {
    null = true
    type = timestamp(0)
  }
  column "updated_at" {
    null = true
    type = timestamp(0)
  }
  primary_key {
    columns = [column.id]
  }
  index "personal_access_tokens_tokenable_type_tokenable_id_index" {
    columns = [column.tokenable_type, column.tokenable_id]
  }
  unique "personal_access_tokens_token_unique" {
    columns = [column.token]
  }
}
table "users" {
  schema = schema.public
  column "user_id" {
    null = false
    type = character_varying(36)
  }
  column "email" {
    null = false
    type = character_varying(255)
  }
  column "password" {
    null = false
    type = character_varying(255)
  }
  column "created_at" {
    null = true
    type = timestamp(0)
  }
  column "updated_at" {
    null = true
    type = timestamp(0)
  }
  unique "users_email_unique" {
    columns = [column.email]
  }
}
