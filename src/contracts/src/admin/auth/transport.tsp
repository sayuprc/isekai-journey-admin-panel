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

model RegisterStartRequest {
  token: registrationToken;
  email: email;
  name: adminUserName;
}

model RegisterStartResponse {
  authCeremonyId: authCeremonyId;
  publicKey: webAuthnPublicKeyOptions;
}

model RegisterFinishRequest {
  authCeremonyId: authCeremonyId;
  credential: webAuthnCredential;
}

model RegisterFinishResponse {
  accessToken: accessToken;
  refreshTokenId: refreshTokenId;
  refreshToken: refreshToken;
}
