import "@typespec/http";
import "../auth";
import "../lib";

using Http;

namespace IsekaiObservatory.Admin;

@doc("メディア検索のソート条件")
enum MediaSearchSortBy {
  title: "title",
}

@tag("Media")
@route("/media")
@useAuth(BearerAuth)
interface MediaService {
  @doc("メディア検索API")
  @route("/search")
  searchMedia(
    @query title?: string,
    @query type?: MediaTypeValue,
    @query("is_display") isDisplay?: boolean,
    @query sort?: MediaSearchSortBy = MediaSearchSortBy.title,
    @query order?: SortOrder = SortOrder.asc,
    @query page?: page = 1,
    @query("per_page") perPage?: PerPage = PerPage.fifty,
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
