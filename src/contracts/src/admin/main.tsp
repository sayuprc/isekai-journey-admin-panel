import "@typespec/http";
import "@typespec/versioning";
import "./admin-users";
import "./auth";
import "./creators";
import "./persons";
import "./performers";
import "./song-tags";
import "./song-types";
import "./songs";

using Http;
using Versioning;

@service(#{ title: "ヰ世界観測所 管理画面向け API" })
@server(
  "https://local.api.isekaijoucho.fan/admin/{version}",
  "ローカル環境",
  {
    version: Version,
  }
)
@versioned(Version)
namespace IsekaiObservatory.Admin;

enum Version {
  v1,
}
