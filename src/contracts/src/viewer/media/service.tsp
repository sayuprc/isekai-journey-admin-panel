import "@typespec/http";

using Http;

namespace IsekaiObservatory.Viewer;

@tag("Media")
@route("/media")
interface MediaService {
  @doc("メディア一覧取得API")
  listMedia(@query cursor?: cursor, @query limit?: limit): Ok<MediaListResponse> | InternalServerError;

  @doc("メディア詳細取得API")
  getMedia(@path mediaId: mediaId): Ok<MediaDetailResponse> | NotFound | InternalServerError;
}
