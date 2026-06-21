import "../../shared";
import "../../packages/media";
import "../../packages/song";

using IsekaiObservatory.Shared;
using IsekaiObservatory.Packages.Media;
using IsekaiObservatory.Packages.Song;

namespace IsekaiObservatory.Viewer;

model SongRelationCounts {
  // @doc("関連リリース数")
  // releaseCount: int32;

  @doc("関連メディア数")
  mediaCount: int32;
}

@example(#{
  songId: "3cd42c09-ff3c-4cd2-913f-a279c4ea89b4",
  title: "テスト楽曲",
  description: "テスト楽曲説明",
  type: #{ name: "オリジナル曲", value: SongTypeValue.Original },
  counts: #{
    // releaseCount: 1,
    mediaCount: 1,
  },
  lyricists: #["テスト作詞者"],
  composers: #["テスト作曲者"],
  arrangers: #["テスト編曲者"],
  media: #[
    #{
      mediaId: "11111111-1111-1111-1111-111111111111",
      title: "テストメディアMV",
      type: #{ name: "動画", value: MediaTypeValue.Video },
      format: #{ name: "MV", value: MediaFormatValue.Mv },
      publishedAt: "2024-03-01",
    }
  ],
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
  media: SongMediaSummary[];
}

@example(#{
  mediaId: "11111111-1111-1111-1111-111111111111",
  title: "テストメディアMV",
  type: #{ name: "動画", value: MediaTypeValue.Video },
  format: #{ name: "MV", value: MediaFormatValue.Mv },
  publishedAt: "2024-03-01",
})
model SongMediaSummary {
  mediaId: mediaId;
  title: mediaTitle;
  type: MediaType;
  format: MediaFormat;
  publishedAt: mediaPublishedAt;
}
