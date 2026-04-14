import "@typespec/http";
import "@typespec/versioning";
import "./admin-users";
import "./auth";
import "./creators";
import "./performers";
import "./song-attributes";
import "./song-types";
import "./songs";

using Http;
using Versioning;

@service(#{ title: "ヰ世界のテラリウム 管理画面向け API" })
@server(
  "https://local.api.terrarium.isekaijoucho.fan/admin/{version}",
  "ローカル環境",
  {
    version: Version,
  }
)
@versioned(Version)
namespace IsekaiTerrarium.Admin;

enum Version {
  v1,
}
