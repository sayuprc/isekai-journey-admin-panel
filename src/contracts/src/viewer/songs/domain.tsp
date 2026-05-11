namespace IsekaiObservatory.Viewer;

@doc("楽曲ID")
@format("uuid")
scalar songId extends string;

@doc("楽曲名")
@minLength(1)
scalar title extends string;

@doc("楽曲の説明")
scalar description extends string;

@doc("楽曲種別名")
@minLength(1)
scalar songTypeName extends string;

@doc("楽曲種別の値")
enum SongTypeValue {
  original: 1,
  cover: 2,
}

@example(#{ name: "オリジナル曲", value: SongTypeValue.original })
model SongType {
  name: songTypeName;
  value: SongTypeValue;
}

model SongRelationCounts {
  // @doc("関連リリース数")
  // releaseCount: int32;

  @doc("関連メディア数")
  mediaCount: int32;
}

@example(#{
  songId: "3cd42c09-ff3c-4cd2-913f-a279c4ea89b4",
  title: "描き続けた君へ",
  description: "楽曲説明",
  type: #{ name: "オリジナル曲", value: SongTypeValue.original },
  counts: #{
    // releaseCount: 1,
    mediaCount: 3,
  },
  lyricists: #["作詞者"],
  composers: #["作曲者"],
  arrangers: #["編曲者"],
})
model SongListItem {
  songId: songId;
  title: title;
  description: description;
  type: SongType;
  counts: SongRelationCounts;
  lyricists: string[];
  composers: string[];
  arrangers: string[];
}

@doc("関連メディアID")
@format("uuid")
scalar mediaId extends string;

@doc("関連メディアタイトル")
@minLength(1)
scalar mediaTitle extends string;

@doc("関連メディア種別名")
@minLength(1)
scalar mediaTypeName extends string;

@doc("関連メディア種別の値")
enum MediaTypeValue {
  video: 1,
  article: 2,
  socialPost: 3,
  officialPage: 4,
  other: 99,
}

@example(#{ name: "動画", value: MediaTypeValue.video })
model MediaType {
  name: mediaTypeName;
  value: MediaTypeValue;
}

@doc("関連メディア公開日")
@format("date")
scalar mediaPublishedAt extends string;

@example(#{
  mediaId: "11111111-1111-1111-1111-111111111111",
  title: "描き続けた君へ MV",
  type: #{ name: "動画", value: MediaTypeValue.video },
  publishedAt: "2024-03-01",
})
model SongDetailMediaSummary {
  mediaId: mediaId;
  title: mediaTitle;
  type: MediaType;
  publishedAt: mediaPublishedAt;
}

@example(#{
  songId: "3cd42c09-ff3c-4cd2-913f-a279c4ea89b4",
  title: "描き続けた君へ",
  description: "楽曲説明",
  type: #{ name: "オリジナル曲", value: SongTypeValue.original },
  counts: #{ mediaCount: 1 },
  lyricists: #["作詞者"],
  composers: #["作曲者"],
  arrangers: #["編曲者"],
  media: #[
    #{
      mediaId: "11111111-1111-1111-1111-111111111111",
      title: "描き続けた君へ MV",
      type: #{ name: "動画", value: MediaTypeValue.video },
      publishedAt: "2024-03-01",
    }
  ],
})
model SongDetail {
  songId: songId;
  title: title;
  description: description;
  type: SongType;
  counts: SongRelationCounts;
  lyricists: string[];
  composers: string[];
  arrangers: string[];
  media: SongDetailMediaSummary[];
}
