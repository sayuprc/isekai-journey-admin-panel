import "@typespec/http";
import "../auth";
import "../lib";

using Http;

namespace IsekaiObservatory.Admin;

@tag("Release")
@route("/releases")
@useAuth(BearerAuth)
interface ReleaseService {
  @doc("リリース作成API")
  createRelease(@body request: ReleaseCreateRequest):
    | Ok<ReleaseCreateResponse>
    | BadRequest
    | Unauthorized
    | Forbidden
    | Unprocessable
    | ServerError;

  @doc("リリース取得API")
  getRelease(@path releaseId: uuid):
    | Ok<ReleaseGetResponse>
    | Unauthorized
    | Forbidden
    | NotFound
    | Unprocessable
    | ServerError;

  @doc("リリース検索API")
  @route("/search")
  searchReleases(
    @query title?: string,
    @query type?: ReleaseTypeValue,
    @query("distribution_type") distributionType?: ReleaseDistributionTypeValue,
    @query("is_display") isDisplay?: boolean,
    @query page?: page = 1,
    @query("per_page") perPage?: PerPage = PerPage.twentyFive,
  ): Ok<ReleaseSearchResponse> | Unauthorized | Forbidden | ServerError;
}
