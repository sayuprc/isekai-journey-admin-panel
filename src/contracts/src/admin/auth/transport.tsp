import "@typespec/http";
import "../lib";

using Http;

namespace IsekaiTerrarium.Admin;

model LoginRequest {
  email: email;
  password: password;
}

model LoginResponse {
  accessToken: accessToken;
  refreshToken: refreshToken;
}
