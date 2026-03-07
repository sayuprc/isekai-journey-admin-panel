variable "table_schemas" {
  type = list(string)
  default = [
    "file://schemas/schema.my.hcl",
    "file://schemas/admin-users.my.hcl",
    "file://schemas/admin-user-permissions.my.hcl",
    "file://schemas/creators.my.hcl",
    "file://schemas/performers.my.hcl",
    "file://schemas/songs.my.hcl",
    "file://schemas/song-lyricists.my.hcl",
    "file://schemas/song-composers.my.hcl",
    "file://schemas/song-arrangers.my.hcl",
    "file://schemas/refresh-tokens.my.hcl",
  ]
}

env "local" {
  src = var.table_schemas
  url = "mysql://${getenv("DB_USERNAME")}:${getenv("DB_PASSWORD")}@localhost:${getenv("DB_PORT")}/${getenv("DB_DATABASE")}"
}

env "testing" {
  src = var.table_schemas
  url = "mysql://${getenv("DB_USERNAME")}:${getenv("DB_PASSWORD")}@localhost:${getenv("DB_PORT")}/${getenv("DB_DATABASE")}"
}
