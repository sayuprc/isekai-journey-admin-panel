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

model MediaRelationCounts {
  @doc("関連楽曲数")
  songCount: int32;
}

@example(#{
  songId: "3cd42c09-ff3c-4cd2-913f-a279c4ea89b4",
  title: "描き続けた君へ",
  type: #{ name: "オリジナル曲", value: SongTypeValue.original },
})
model MediaDetailSongSummary {
  songId: songId;
  title: title;
  type: SongType;
}

@example(#{
  mediaId: "11111111-1111-1111-1111-111111111111",
  title: "描き続けた君へ MV",
  url: "https://example.com/media/1",
  publishedAt: "2024-03-01",
  type: #{ name: "動画", value: MediaTypeValue.video },
  format: #{ name: "MV", value: MediaFormatValue.mv },
  counts: #{ songCount: 1 },
  songs: #[
    #{
      songId: "3cd42c09-ff3c-4cd2-913f-a279c4ea89b4",
      title: "描き続けた君へ",
      type: #{ name: "オリジナル曲", value: SongTypeValue.original },
    }
  ],
})
model MediaDetail {
  mediaId: mediaId;
  title: mediaTitle;
  url: mediaUrl;
  publishedAt: mediaPublishedAt;
  type: MediaType;
  format: MediaFormat;
  counts: MediaRelationCounts;
  songs: MediaDetailSongSummary[];
}
