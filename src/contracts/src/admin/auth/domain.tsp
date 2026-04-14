import "../lib";

namespace IsekaiTerrarium.Admin;

@doc("平文パスワード")
@secret
scalar password extends string;

@doc("アクセストークン(JWT)")
@secret
scalar accessToken extends string;

@doc("リフレッシュトークン")
@secret
scalar refreshToken extends string;
