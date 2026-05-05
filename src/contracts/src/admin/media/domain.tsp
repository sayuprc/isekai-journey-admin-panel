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

@doc("メディア種別の値")
enum MediaTypeValue {
  Video: 1,
  Article: 2,
  SocialPost: 3,
  OfficialPage: 4,
  Other: 99,
}

@example(#{ name: "動画", value: MediaTypeValue.Video })
model MediaType {
  name: mediaTypeName;
  value: MediaTypeValue;
}

@doc("楽曲文脈メディア種別名")
@minLength(1)
scalar songMediaTypeName extends string;

@doc("楽曲文脈メディア種別の値")
enum SongMediaTypeValue {
  Mv: 1,
  AudioVideo: 2,
  StreamArchive: 3,
  ShortVideo: 4,
  Other: 99,
}

@example(#{ name: "MV", value: SongMediaTypeValue.Mv })
model SongMediaType {
  name: songMediaTypeName;
  value: SongMediaTypeValue;
}

@example(#{
  mediaId: "4d31f7b7-b15e-4bd8-a0b9-0b2a6fdb2b45",
  title: "描き続けた君へ Official MV",
  url: "https://example.com/media/1",
  type: #{ name: "動画", value: MediaTypeValue.Video },
  songMediaType: #{ name: "MV", value: SongMediaTypeValue.Mv },
  isDisplay: true,
  orderNo: 1,
})
model SongAttachedMedia {
  mediaId: mediaId;
  title: mediaTitle;
  url: mediaUrl;
  type: MediaType;
  songMediaType: SongMediaType;
  isDisplay: boolean;
  orderNo: orderNo;
}
