import "@typespec/http";
import "../admin-users";
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

model RegisterRequest {
  name: adminUserName;
  email: email;
  password: password;
  registrationToken: registrationToken;
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
