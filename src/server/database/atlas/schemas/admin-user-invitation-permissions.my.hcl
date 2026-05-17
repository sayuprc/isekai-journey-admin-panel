table "admin_user_invitation_permissions" {
  schema  = schema.db
  comment = "管理ユーザー招待権限"

  column "invitation_id" {
    null    = false
    type    = binary(16)
    comment = "招待ID"
  }
  column "permission" {
    null    = false
    type    = varchar(255)
    comment = "権限"
  }

  primary_key {
    columns = [column.invitation_id, column.permission]
  }

  foreign_key "fk_admin_user_invitation_permissions_invitation_id" {
    columns     = [column.invitation_id]
    ref_columns = [table.admin_user_invitations.column.invitation_id]
    on_delete   = CASCADE
  }
}
