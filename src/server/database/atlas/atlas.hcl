variable "schemas" {
  type = list(string)
  default = [
    "file://schemas/schema.hcl",
    "file://schemas/default.hcl",
    "file://schemas/journey_logs.hcl",
    "file://schemas/journey_log_links.hcl",
    "file://schemas/journey_log_link_types.hcl",
    "file://schemas/creators.hcl",
    "file://schemas/songs.hcl",
    "file://schemas/song_creators.hcl",
    "file://schemas/archives.hcl",
    "file://schemas/youtube_archive_details.hcl",
    "file://schemas/twitter_archive_details.hcl",
    "file://schemas/non_link_archive_details.hcl",
  ]
}

variable "db_user" {
  type = string
}

variable "db_password" {
  type = string
}

env "local" {
  src = var.schemas

  url = "postgres://${var.db_user}:${var.db_password}@db:5432/isekai_journey_admin?search_path=public&sslmode=disable"
}

env "testing" {
  src = var.schemas

  url = "postgres://${var.db_user}:${var.db_password}@test-db:5432/isekai_journey_admin?search_path=public&sslmode=disable"
}
