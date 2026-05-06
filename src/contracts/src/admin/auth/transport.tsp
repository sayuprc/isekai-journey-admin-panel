import "@typespec/http";
import "../lib";

using Http;

namespace IsekaiObservatory.Admin;

model LoginRequest {
  email: email;
  password: password;
}

model PasskeyRegistrationStartRequest {
  email: email;
  registrationToken: registrationToken;
}

model PasskeyRegistrationStartResponse {
  authCeremonyId: authCeremonyId;
  publicKey: webAuthnPublicKeyOptions;
}

model PasskeyRegistrationFinishRequest {
  authCeremonyId: authCeremonyId;
  credential: webAuthnCredential;
}

model PasskeyRegistrationFinishResponse {
  accessToken: accessToken;
  refreshTokenId: refreshTokenId;
  refreshToken: refreshToken;
}

model PasskeyLoginStartRequest {
  email: email;
}

model PasskeyLoginStartResponse {
  authCeremonyId: authCeremonyId;
  publicKey: webAuthnPublicKeyOptions;
}

model PasskeyLoginFinishRequest {
  authCeremonyId: authCeremonyId;
  credential: webAuthnCredential;
}

model PasskeyLoginFinishResponse {
  accessToken: accessToken;
  refreshTokenId: refreshTokenId;
  refreshToken: refreshToken;
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
