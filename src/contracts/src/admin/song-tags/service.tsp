import "@typespec/http";
import "../auth";
import "../lib";

using Http;

namespace IsekaiObservatory.Admin;

@doc("楽曲タグ検索のソート条件")
enum SongTagSearchSortBy {
  name: "name",
  orderNo: "order_no",
}

@tag("SongTag")
@route("/song-tags")
@useAuth(BearerAuth)
interface SongTagService {
  @doc("楽曲タグ一覧取得API")
  listSongTags(): Ok<SongTagListResponse> | Unauthorized | Forbidden | ServerError;

  @doc("楽曲タグ検索API")
  @route("/search")
  searchSongTags(
    @query name?: string,
    @query sort?: SongTagSearchSortBy = SongTagSearchSortBy.orderNo,
    @query order?: SortOrder = SortOrder.asc,
    @query page?: page = 1,
    @query("per_page") perPage?: PerPage = PerPage.fifty,
  ): Ok<SongTagSearchResponse> | Unauthorized | Forbidden | ServerError;
}
