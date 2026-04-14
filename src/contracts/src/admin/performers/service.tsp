import "@typespec/http";
import "../lib";
import "../auth";

using Http;

namespace IsekaiTerrarium.Admin;

@doc("共演者検索のソート条件")
enum PerformerSearchSortBy {
  name: "name",
  orderNo: "order_no",
}

@tag("Performer")
@route("/performers")
@useAuth(BearerAuth)
interface PerformerService {
  @doc("共演者一覧取得API")
  listPerformers(): Ok<PerformerListResponse> | Unauthorized | Forbidden | ServerError;

  @doc("共演者検索API")
  @route("/search")
  searchPerformers(
    @query name?: string,
    @query sort?: PerformerSearchSortBy = PerformerSearchSortBy.orderNo,
    @query order?: SortOrder = SortOrder.asc,
    @query page?: page = 1,
    @query("per_page") perPage?: PerPage = PerPage.fifty,
  ): Ok<PerformerSearchResponse> | Unauthorized | Forbidden | ServerError;

  @doc("共演者取得API")
  getPerformer(@path performerId: uuid):
    | Ok<PerformerGetResponse>
    | Unauthorized
    | Forbidden
    | NotFound
    | Unprocessable
    | ServerError;

  @doc("共演者作成API")
  createPerformer(@body request: PerformerCreateRequest):
    | Ok<PerformerCreateResponse>
    | BadRequest
    | Unauthorized
    | Forbidden
    | Unprocessable
    | ServerError;

  @doc("共演者更新API")
  @put
  updatePerformer(@path performerId: uuid, @body request: PerformerUpdateRequest):
    | Ok<PerformerUpdateResponse>
    | BadRequest
    | Unauthorized
    | Forbidden
    | NotFound
    | Unprocessable
    | ServerError;

  @doc("共演者削除API")
  @delete
  deletePerformer(@path performerId: uuid):
    | NoContent
    | BadRequest
    | Unauthorized
    | Forbidden
    | Unprocessable
    | ServerError;
}
