import "@typespec/http";
import "@typespec/versioning";
import "./media";
import "./site-stats";
import "./songs";

using Http;
using Versioning;

@service(#{ title: "ヰ世界観測所 API" })
@server(
  "https://local.api.isekaijoucho.fan/{version}",
  "ローカル環境",
  {
    version: Version,
  }
)
@versioned(Version)
namespace IsekaiObservatory.Viewer;

enum Version {
  v1,
}
