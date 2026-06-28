import "../../shared";

using IsekaiObservatory.Shared;

namespace IsekaiObservatory.Packages.Media;

@doc("メディアID")
@friendlyName("mediaId")
scalar mediaId extends uuid;

@doc("メディアタイトル")
@minLength(1)
@friendlyName("mediaTitle")
scalar mediaTitle extends string;

@doc("メディアURL")
@format("uri")
@friendlyName("mediaUrl")
scalar mediaUrl extends string;

@doc("公開日")
@format("date")
@friendlyName("mediaPublishedAt")
scalar mediaPublishedAt extends string;

@doc("メディア種別名")
@minLength(1)
@friendlyName("mediaTypeName")
scalar mediaTypeName extends string;

@doc("メディア種別の値")
@friendlyName("MediaTypeValue")
enum MediaTypeValue {
  Video: 1,
  Article: 2,
  SocialPost: 3,
  OfficialPage: 4,
  Other: 99,
}

@example(#{ name: "動画", value: MediaTypeValue.Video })
@friendlyName("MediaType")
model MediaType {
  name: mediaTypeName;
  value: MediaTypeValue;
}

@doc("メディア形式名")
@minLength(1)
@friendlyName("mediaFormatName")
scalar mediaFormatName extends string;

@doc("メディア形式の値")
@friendlyName("MediaFormatValue")
enum MediaFormatValue {
  Mv: 1,
  AudioVideo: 2,
  StreamArchive: 3,
  ShortVideo: 4,
  LiveClip: 5,
  Other: 99,
}

@example(#{ name: "MV", value: MediaFormatValue.Mv })
@friendlyName("MediaFormat")
model MediaFormat {
  name: mediaFormatName;
  value: MediaFormatValue;
}

@doc("プラットフォーム種別名")
@minLength(1)
@friendlyName("mediaPlatformName")
scalar mediaPlatformName extends string;

@doc("プラットフォーム種別の値")
@friendlyName("MediaPlatformValue")
enum MediaPlatformValue {
  YouTube: 1,
  X: 2,
  Other: 99,
}

@example(#{ name: "YouTube", value: MediaPlatformValue.YouTube })
@friendlyName("MediaPlatform")
model MediaPlatform {
  name: mediaPlatformName;
  value: MediaPlatformValue;
}

@doc("サムネイルURL")
@format("uri")
@friendlyName("mediaThumbnailUrl")
scalar mediaThumbnailUrl extends string;
