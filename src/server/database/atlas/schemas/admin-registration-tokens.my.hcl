table "admin_registration_tokens" {
  schema  = schema.db
  comment = "管理画面ユーザー登録トークン"

  column "admin_registration_token_id" {
    null    = false
    type    = binary(16)
    comment = "管理画面ユーザー登録トークンID"
  }
  column "email" {
    null    = false
    type    = varchar(255)
    comment = "発行対象メールアドレス"
  }
  column "role" {
    null     = false
    type     = tinyint
    unsigned = true
    comment  = "ロール"
  }
  column "token" {
    null    = false
    type    = varchar(255)
    comment = "登録トークン"
  }
  column "expired_at" {
    null    = false
    type    = datetime
    comment = "有効期限"
  }
  column "status" {
    null     = false
    type     = tinyint
    unsigned = true
    comment  = "消費状態"
  }
  column "created_at" {
    null    = false
    type    = datetime
    comment = "作成日時"
  }
  column "updated_at" {
    null    = false
    type    = datetime
    comment = "更新日時"
  }

  primary_key {
    columns = [column.admin_registration_token_id]
  }

  index "admin_registration_tokens_email_status_expired_at_index" {
    columns = [column.email, column.status, column.expired_at]
  }
}
