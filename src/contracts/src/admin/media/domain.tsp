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

@doc("メディア形式名")
@minLength(1)
scalar mediaFormatName extends string;

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

enum MediaFormatValue {
  Mv: 1,
  AudioVideo: 2,
  StreamArchive: 3,
  ShortVideo: 4,
  LiveClip: 5,
  Other: 99,
}

model MediaFormat {
  name: mediaFormatName;
  value: MediaFormatValue;
}

model Media {
  mediaId: mediaId;
  title: mediaTitle;
  url: mediaUrl;
  type: MediaType;
  format: MediaFormat;
  isDisplay: boolean;
}

@doc("メディアを参照している楽曲ID")
scalar mediaReferencedSongId extends uuid;

@doc("メディアを参照している楽曲名")
@minLength(1)
scalar mediaReferencedSongTitle extends string;

model MediaReferencedSong {
  songId: mediaReferencedSongId;
  title: mediaReferencedSongTitle;
  songOrderNo: orderNo;
  mediaOrderNo: orderNo;
}
