import "@typespec/http";

using Http;

namespace IsekaiObservatory.Viewer;

@tag("Song")
@route("/songs")
interface SongService {
  @doc("Viewer 楽曲一覧取得API")
  @summary("SSG の build 時一括取得を既定とした楽曲一覧")
  listSongs(@query cursor?: cursor, @query limit?: limit): Ok<SongListResponse> | InternalServerError;
}
