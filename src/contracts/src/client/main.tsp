import "@typespec/http";
import "@typespec/versioning";

using Http;
using Versioning;

@service(#{ title: "ヰ世界のテラリウム API" })
@server(
  "https://local.api.terrarium.isekaijoucho.fan/{version}",
  "ローカル環境",
  {
    version: Version,
  }
)
@versioned(Version)
namespace IsekaiTerrarium.Client;

enum Version {
  v1,
}
