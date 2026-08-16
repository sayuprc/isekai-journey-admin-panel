import "@typespec/http";
import "../auth";
import "../../shared";

using IsekaiObservatory.Shared;
using Http;

namespace IsekaiObservatory.Admin;

@doc("場所検索のソート条件")
enum PlaceSearchSortBy {
  name: "name",
}

@tag("Place")
@route("/places")
@useAuth(BearerAuth)
interface PlaceService {
  @doc("場所一覧取得API")
  listPlaces(): Ok<PlaceListResponse> | Unauthorized | Forbidden | ServerError;

  @doc("場所検索API")
  @route("/search")
  searchPlaces(
    @query name?: string,
    @query kindValue?: PlaceKindValue,
    @query sort?: PlaceSearchSortBy = PlaceSearchSortBy.name,
    @query order?: SortOrder = SortOrder.asc,
    @query page?: page = 1,
    @query("per_page") perPage?: PerPage = PerPage.fifty,
  ): Ok<PlaceSearchResponse> | Unauthorized | Forbidden | ServerError;

  @doc("場所取得API")
  getPlace(@path placeId: uuid):
    | Ok<PlaceGetResponse>
    | Unauthorized
    | Forbidden
    | NotFound
    | Unprocessable
    | ServerError;

  @doc("場所作成API")
  createPlace(@body request: PlaceCreateRequest):
    | Ok<PlaceCreateResponse>
    | BadRequest
    | Unauthorized
    | Forbidden
    | Unprocessable
    | ServerError;

  @doc("場所更新API")
  @put
  updatePlace(@path placeId: uuid, @body request: PlaceUpdateRequest):
    | Ok<PlaceUpdateResponse>
    | BadRequest
    | Unauthorized
    | Forbidden
    | NotFound
    | Unprocessable
    | ServerError;

  @doc("場所削除API")
  @delete
  deletePlace(@path placeId: uuid): NoContent | BadRequest | Unauthorized | Forbidden | Unprocessable | ServerError;
}
