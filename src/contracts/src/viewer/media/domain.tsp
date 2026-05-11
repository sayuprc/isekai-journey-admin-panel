namespace IsekaiObservatory.Viewer;

@doc("メディアURL")
@format("uri")
scalar mediaUrl extends string;

@doc("メディア形式名")
@minLength(1)
scalar mediaFormatName extends string;

@doc("メディア形式の値")
enum MediaFormatValue {
  mv: 1,
  audioVideo: 2,
  streamArchive: 3,
  shortVideo: 4,
  liveClip: 5,
  other: 99,
}

@example(#{ name: "MV", value: MediaFormatValue.mv })
model MediaFormat {
  name: mediaFormatName;
  value: MediaFormatValue;
}

@example(#{
  mediaId: "11111111-1111-1111-1111-111111111111",
  title: "描き続けた君へ MV",
  url: "https://example.com/media/1",
  publishedAt: "2024-03-01",
  type: #{ name: "動画", value: MediaTypeValue.video },
  format: #{ name: "MV", value: MediaFormatValue.mv },
})
model MediaListItem {
  mediaId: mediaId;
  title: mediaTitle;
  url: mediaUrl;
  publishedAt: mediaPublishedAt;
  type: MediaType;
  format: MediaFormat;
}
