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
    @query type?: MediaTypeValue,
    @query format?: MediaFormatValue,
    @query("is_display") isDisplay?: boolean,
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
