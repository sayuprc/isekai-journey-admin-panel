import "@typespec/http";

using Http;

namespace IsekaiObservatory.Viewer;

@tag("Song")
@route("/songs")
interface SongService {
  @doc("楽曲一覧取得API")
  listSongs(@query cursor?: cursor, @query limit?: limit): Ok<SongListResponse> | InternalServerError;

  @doc("楽曲詳細取得API")
  getSong(@path songId: songId): Ok<SongDetailResponse> | NotFound | InternalServerError;
}
