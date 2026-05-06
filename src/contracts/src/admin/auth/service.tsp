import "@typespec/http";
import "../lib";

using Http;

namespace IsekaiObservatory.Admin;

@tag("Authenticate")
@route("/auth")
interface AuthenticateService {
  @doc("パスキー登録開始API")
  @route("/register/start")
  registerStart(@body request: PasskeyRegistrationStartRequest):
    | PasskeyRegistrationStartResponse
    | BadRequest
    | Unauthorized
    | Unprocessable
    | ServerError;

  @doc("パスキー登録完了API")
  @route("/register/finish")
  registerFinish(@body request: PasskeyRegistrationFinishRequest):
    | PasskeyRegistrationFinishResponse
    | BadRequest
    | Unauthorized
    | Unprocessable
    | ServerError;

  @doc("パスキーログイン開始API")
  @route("/login/start")
  loginStart(@body request: PasskeyLoginStartRequest):
    | PasskeyLoginStartResponse
    | BadRequest
    | Unauthorized
    | Unprocessable
    | ServerError;

  @doc("パスキーログイン完了API")
  @route("/login/finish")
  loginFinish(@body request: PasskeyLoginFinishRequest):
    | PasskeyLoginFinishResponse
    | BadRequest
    | Unauthorized
    | Unprocessable
    | ServerError;

  @doc("アクセストークン再発行API")
  @route("/refresh")
  refresh(@body request: RefreshTokenRequest): RefreshTokenResponse | Unauthorized | Unprocessable | ServerError;
}
