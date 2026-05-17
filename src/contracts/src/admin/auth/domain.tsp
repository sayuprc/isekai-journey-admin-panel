import "../lib";

namespace IsekaiObservatory.Admin;

@doc("平文パスワード")
@secret
scalar password extends string;

@doc("アクセストークン(JWT)")
@secret
scalar accessToken extends string;

@doc("リフレッシュトークン")
@secret
scalar refreshToken extends string;

@doc("リフレッシュトークンID")
scalar refreshTokenId extends uuid;

@doc("招待トークン")
@secret
scalar invitationToken extends string;
