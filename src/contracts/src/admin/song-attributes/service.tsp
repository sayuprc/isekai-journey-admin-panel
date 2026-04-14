import "@typespec/http";
import "../lib";
import "../auth";

using Http;

namespace IsekaiTerrarium.Admin;

@tag("SongAttribute")
@route("/song-attributes")
@useAuth(BearerAuth)
interface SongAttributeService {
  @doc("楽曲属性一覧取得API")
  listSongAttributes(): Ok<SongAttributeListResponse> | Unauthorized | Forbidden | ServerError;
}
