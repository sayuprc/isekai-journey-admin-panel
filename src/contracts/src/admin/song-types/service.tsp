import "@typespec/http";
import "../lib";
import "../auth";

using Http;

namespace IsekaiTerrarium.Admin;

@tag("SongType")
@route("/song-types")
@useAuth(BearerAuth)
interface SongTypeService {
  @doc("楽曲種別一覧取得API")
  listSongTypes(): Ok<SongTypeListResponse> | Unauthorized | Forbidden | ServerError;
}
