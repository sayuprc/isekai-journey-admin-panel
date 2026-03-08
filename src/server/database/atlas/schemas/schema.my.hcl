variable "db_name" {
  type = string
}

schema "db" {
  name    = var.db_name
  charset = "utf8mb4"
  collate = "utf8mb4_bin"
}
