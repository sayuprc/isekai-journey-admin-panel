import "@typespec/http";
import "../lib";
import "../auth";

using Http;

namespace IsekaiTerrarium.Admin;

@tag("AdminUser")
@route("/admin-users")
@useAuth(BearerAuth)
interface AdminUserService {
  @doc("管理ユーザー一覧取得API")
  listAdminUsers(): Ok<AdminUserListResponse> | Unauthorized | Forbidden | ServerError;
}
