import "@typespec/http";
import "../lib";

using Http;

namespace IsekaiObservatory.Admin;

model LoginRequest {
  email: email;
  password: password;
}

model LoginResponse {
  accessToken: accessToken;
  refreshTokenId: refreshTokenId;
  refreshToken: refreshToken;
}

model RefreshTokenRequest {
  refreshTokenId: refreshTokenId;
  refreshToken: refreshToken;
}

model RefreshTokenResponse {
  accessToken: accessToken;
  refreshTokenId: refreshTokenId;
  refreshToken: refreshToken;
}

model RegisterRequest {
  token: registrationToken;
  email: email;
  name: adminUserName;
  password: password;
}

model RegisterResponse {
  accessToken: accessToken;
  refreshTokenId: refreshTokenId;
  refreshToken: refreshToken;
}
