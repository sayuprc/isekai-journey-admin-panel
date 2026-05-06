table "admin_user_registration_tokens" {
  schema  = schema.db
  comment = "管理ユーザー登録トークン"

  column "admin_user_registration_token_id" {
    null    = false
    type    = binary(16)
    comment = "管理ユーザー登録トークンID"
  }
  column "name" {
    null    = false
    type    = varchar(255)
    comment = "管理者名"
  }
  column "email" {
    null    = false
    type    = varchar(255)
    comment = "メールアドレス"
  }
  column "role" {
    null     = false
    type     = tinyint
    unsigned = true
    comment  = "ロール"
  }
  column "token_hash" {
    null    = false
    type    = varchar(255)
    comment = "登録トークンハッシュ"
  }
  column "expired_at" {
    null    = false
    type    = datetime
    comment = "有効期限"
  }
  column "used_at" {
    null    = true
    type    = datetime
    comment = "使用日時"
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
    columns = [column.admin_user_registration_token_id]
  }

  index "admin_user_registration_tokens_email_index" {
    columns = [column.email]
  }
}
