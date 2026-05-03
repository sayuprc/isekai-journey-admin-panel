import "@typespec/http";
import "../lib";

using Http;

namespace IsekaiObservatory.Admin;

@tag("Authenticate")
@route("/auth")
interface AuthenticateService {
  @doc("認証API")
  @route("/login")
  login(@body request: LoginRequest): LoginResponse | BadRequest | Unauthorized | Unprocessable | ServerError;

  @doc("アクセストークン再発行API")
  @route("/refresh")
  refresh(@body request: RefreshTokenRequest): RefreshTokenResponse | Unauthorized | Unprocessable | ServerError;
}
