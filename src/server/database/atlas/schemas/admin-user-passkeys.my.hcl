table "admin_user_passkeys" {
  schema  = schema.db
  comment = "管理ユーザーパスキー"

  column "admin_user_passkey_id" {
    null    = false
    type    = binary(16)
    comment = "管理ユーザーパスキーID"
  }
  column "admin_user_id" {
    null    = false
    type    = binary(16)
    comment = "管理ユーザーID"
  }
  // credential_id は base64url 文字列として保持し、MySQL の unique index を素直に張れるよう varchar にする。
  column "credential_id" {
    null    = false
    type    = varchar(512)
    comment = "WebAuthn credential ID"
  }
  // 公開鍵は COSE 形式のシリアライズ結果を保持する想定。
  column "public_key" {
    null    = false
    type    = text
    comment = "WebAuthn 公開鍵"
  }
  column "sign_count" {
    null     = false
    type     = bigint
    unsigned = true
    comment  = "署名カウンタ"
  }
  column "last_used_at" {
    null    = true
    type    = datetime
    comment = "最終利用日時"
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
    columns = [column.admin_user_passkey_id]
  }

  index "admin_user_passkeys_admin_user_id_index" {
    columns = [column.admin_user_id]
  }

  index "admin_user_passkeys_credential_id_unique" {
    unique  = true
    columns = [column.credential_id]
  }

  foreign_key "fk_admin_user_passkeys_admin_user_id" {
    columns     = [column.admin_user_id]
    ref_columns = [table.admin_users.column.admin_user_id]
    on_delete   = CASCADE
  }
}
