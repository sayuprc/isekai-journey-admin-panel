import "../../packages/media";
import "../../packages/song";

using IsekaiObservatory.Packages.Media;
using IsekaiObservatory.Packages.Song;

namespace IsekaiObservatory.Viewer;

@example(#{
  mediaId: "11111111-1111-1111-1111-111111111111",
  title: "テストメディアMV",
  url: "https://example.com/media/1",
  publishedAt: "2024-03-01",
  type: #{ name: "動画", value: MediaTypeValue.Video },
  format: #{ name: "MV", value: MediaFormatValue.Mv },
  platform: #{ name: "YouTube", value: MediaPlatformValue.YouTube },
  thumbnailUrl: "https://i.ytimg.com/vi/dQw4w9WgXcQ/sddefault.jpg",
  counts: #{ songCount: 1 },
  songs: #[
    #{
      songId: "3cd42c09-ff3c-4cd2-913f-a279c4ea89b4",
      title: "テスト楽曲",
      type: #{ name: "オリジナル曲", value: SongTypeValue.Original },
    }
  ],
})
model MediaListItem {
  mediaId: mediaId;
  title: mediaTitle;
  url: mediaUrl;
  publishedAt: mediaPublishedAt;
  type: MediaType;
  format: MediaFormat;
  platform: MediaPlatform;
  thumbnailUrl?: mediaThumbnailUrl;
  counts: MediaRelationCounts;
  songs: MediaSongSummary[];
}

model MediaRelationCounts {
  @doc("関連楽曲数")
  songCount: int32;
}

@example(#{
  songId: "3cd42c09-ff3c-4cd2-913f-a279c4ea89b4",
  title: "テスト楽曲",
  type: #{ name: "オリジナル曲", value: SongTypeValue.Original },
})
model MediaSongSummary {
  songId: songId;
  title: title;
  type: SongType;
}
