import "@typespec/http";
import "../lib";

using Http;

namespace IsekaiObservatory.Admin;

model LoginStartRequest {
  email: email;
}

model LoginStartResponse {
  authCeremonyId: authCeremonyId;
  publicKey: webAuthnPublicKeyOptions;
}

model LoginFinishRequest {
  authCeremonyId: authCeremonyId;
  credential: webAuthnCredential;
}

model LoginFinishResponse {
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
