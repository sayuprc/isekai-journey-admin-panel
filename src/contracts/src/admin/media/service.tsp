import "@typespec/http";
import "../auth";
import "../lib";

using Http;

namespace IsekaiObservatory.Admin;

@tag("Media")
@route("/media")
@useAuth(BearerAuth)
interface MediaService {
  @doc("メディア検索API")
  @route("/search")
  searchMedia(
    @query title?: string,
    @query page?: page = 1,
    @query("per_page") perPage?: PerPage = PerPage.twentyFive,
  ): Ok<MediaSearchResponse> | Unauthorized | Forbidden | ServerError;

  @doc("メディア作成API")
  createMedia(@body request: MediaCreateRequest):
    | Ok<MediaCreateResponse>
    | BadRequest
    | Unauthorized
    | Forbidden
    | Unprocessable
    | ServerError;
}
