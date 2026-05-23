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

  @doc("管理ユーザー登録開始API")
  @route("/register/start")
  registerStart(@body request: RegisterStartRequest):
    | RegisterStartResponse
    | BadRequest
    | Unauthorized
    | Unprocessable
    | ServerError;

  @doc("管理ユーザー登録完了API")
  @route("/register/finish")
  registerFinish(@body request: RegisterFinishRequest):
    | RegisterFinishResponse
    | BadRequest
    | Unauthorized
    | Unprocessable
    | ServerError;
}
