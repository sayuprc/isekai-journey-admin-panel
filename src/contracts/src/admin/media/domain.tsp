import "../lib";

namespace IsekaiObservatory.Admin;

@doc("メディアID")
scalar mediaId extends uuid;

@doc("メディアタイトル")
@minLength(1)
scalar mediaTitle extends string;

@doc("メディアURL")
@format("uri")
scalar mediaUrl extends string;

@doc("メディア種別名")
@minLength(1)
scalar mediaTypeName extends string;

enum MediaTypeValue {
  Video: 1,
  Article: 2,
  SocialPost: 3,
  OfficialPage: 4,
  Other: 99,
}

model MediaType {
  name: mediaTypeName;
  value: MediaTypeValue;
}

model Media {
  mediaId: mediaId;
  title: mediaTitle;
  url: mediaUrl;
  type: MediaType;
  isDisplay: boolean;
}
