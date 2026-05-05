variable "table_schemas" {
  type = list(string)
  default = [
    "file://schemas/schema.my.hcl",
    "file://schemas/admin-users.my.hcl",
    "file://schemas/admin-user-permissions.my.hcl",
    "file://schemas/admin-user-registration-tokens.my.hcl",
    "file://schemas/persons.my.hcl",
    "file://schemas/songs.my.hcl",
    "file://schemas/media.my.hcl",
    "file://schemas/song-tags.my.hcl",
    "file://schemas/song-taggings.my.hcl",
    "file://schemas/song-persons.my.hcl",
    "file://schemas/song-media-links.my.hcl",
    "file://schemas/refresh-tokens.my.hcl",
    "file://schemas/audit-logs.my.hcl",
  ]
}

env "local" {
  src = var.table_schemas
  url = "mysql://${getenv("DB_USERNAME")}:${getenv("DB_PASSWORD")}@localhost:${getenv("ATLAS_DB_PORT")}/${getenv("DB_DATABASE")}"
}

env "testing" {
  src = var.table_schemas
  url = "mysql://${getenv("DB_USERNAME")}:${getenv("DB_PASSWORD")}@localhost:${getenv("ATLAS_DB_PORT")}/${getenv("DB_DATABASE")}"
}
