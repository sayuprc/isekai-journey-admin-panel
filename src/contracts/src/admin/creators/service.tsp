import "@typespec/http";
import "../lib";
import "../auth";

using Http;

namespace IsekaiTerrarium.Admin;

@doc("クリエイター検索のソート条件")
enum CreatorSearchSortBy {
  name: "name",
  orderNo: "order_no",
}

@tag("Creator")
@route("/creators")
@useAuth(BearerAuth)
interface CreatorService {
  @doc("クリエイター一覧取得API")
  listCreators(): Ok<CreatorListResponse> | Unauthorized | Forbidden | ServerError;

  @doc("クリエイター検索API")
  @route("/search")
  searchCreators(
    @query name?: string,
    @query sort?: CreatorSearchSortBy = CreatorSearchSortBy.orderNo,
    @query order?: SortOrder = SortOrder.asc,
    @query page?: page = 1,
    @query("per_page") perPage?: PerPage = PerPage.fifty,
  ): Ok<CreatorSearchResponse> | Unauthorized | Forbidden | ServerError;

  @doc("クリエイター取得API")
  getCreator(@path creatorId: uuid):
    | Ok<CreatorGetResponse>
    | Unauthorized
    | Forbidden
    | NotFound
    | Unprocessable
    | ServerError;

  @doc("クリエイター作成API")
  createCreator(@body request: CreatorCreateRequest):
    | Ok<CreatorCreateResponse>
    | BadRequest
    | Unauthorized
    | Forbidden
    | Unprocessable
    | ServerError;

  @doc("クリエイター更新API")
  @put
  updateCreator(@path creatorId: uuid, @body request: CreatorUpdateRequest):
    | Ok<CreatorUpdateResponse>
    | BadRequest
    | Unauthorized
    | Forbidden
    | NotFound
    | Unprocessable
    | ServerError;

  @doc("クリエイター削除API")
  @delete
  deleteCreator(@path creatorId: uuid): NoContent | BadRequest | Unauthorized | Forbidden | Unprocessable | ServerError;
}
