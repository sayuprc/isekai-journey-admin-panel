table "admin_user_invitations" {
  schema  = schema.db
  comment = "管理ユーザー招待"

  column "invitation_id" {
    null    = false
    type    = binary(16)
    comment = "招待ID"
  }
  column "token_hash" {
    null    = false
    type    = varchar(255)
    comment = "招待トークンのハッシュ"
  }
  column "role" {
    null     = false
    type     = tinyint
    unsigned = true
    comment  = "ロール"
  }
  column "expires_at" {
    null    = false
    type    = datetime
    comment = "有効期限"
  }
  column "consumed_at" {
    null    = true
    type    = datetime
    comment = "消費日時"
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
    columns = [column.invitation_id]
  }

  index "admin_user_invitations_token_hash_unique" {
    unique  = true
    columns = [column.token_hash]
  }
}
