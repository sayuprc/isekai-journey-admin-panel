variable "schemas" {
  type = list(string)
  default = [
    "file://schemas/schema.hcl",
    "file://schemas/users.hcl",
    "file://schemas/creators.hcl",
    "file://schemas/song-types.hcl",
  ]
}

variable "db_name" {
  type = string
}

env "local" {
  src = var.schemas

  url = "sqlite://../${var.db_name}"
}

env "testing" {
  src = var.schemas

  url = "sqlite://../${var.db_name}"
}
