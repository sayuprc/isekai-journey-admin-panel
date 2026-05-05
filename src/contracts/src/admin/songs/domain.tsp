import "../lib";
import "../media";
import "../persons";
import "../song-tags";

namespace IsekaiObservatory.Admin;

@doc("楽曲ID")
scalar songId extends uuid;

@doc("楽曲名")
@minLength(1)
scalar title extends string;

@doc("楽曲の説明")
scalar description extends string;

@doc("歌詞リンク")
@format("uri")
scalar lyricsLink extends string;

@example(#{
  songId: "3cd42c09-ff3c-4cd2-913f-a279c4ea89b4",
  title: "描き続けた君へ",
  description: "オリジナル楽曲",
  lyricsLink: "https://example.com/lyrics",
  type: #{ name: "オリジナル曲", value: SongTypeValue.Original },
  isDisplay: true,
  orderNo: 1,
  persons: #[
    #{
      personId: "a4d01e9d-e593-4bd9-ba83-e9a7dd3887cd",
      name: "ヰ世界情緒",
      role: SongPersonRole.Lyricist,
      orderNo: 1,
    },
    #{ personId: "a4d01e9d-e593-4bd9-ba83-e9a7dd3887cd", name: "ヰ世界情緒", role: SongPersonRole.Composer, orderNo: 2 }
  ],
  tags: #[#{ songTagId: "3cd42c09-ff3c-4cd2-913f-a279c4ea89b4", name: "派生曲" }],
  media: #[
    #{
      mediaId: "4d31f7b7-b15e-4bd8-a0b9-0b2a6fdb2b45",
      title: "描き続けた君へ Official MV",
      url: "https://example.com/media/1",
      type: #{ name: "動画", value: MediaTypeValue.Video },
      songMediaType: #{ name: "MV", value: SongMediaTypeValue.Mv },
      isDisplay: true,
      orderNo: 1,
    }
  ],
})
model Song {
  songId: songId;
  title: title;
  description: description;
  lyricsLink: lyricsLink | null;
  type: SongType;
  isDisplay: boolean;
  orderNo: orderNo;
  persons: SongPerson[];
  tags: SongAttachedTag[];
  media: SongAttachedMedia[];
}

@doc("楽曲種別名")
@minLength(1)
scalar songTypeName extends string;

@doc("楽曲種別の値")
enum SongTypeValue {
  Original: 1,
  Cover: 2,
}

@example(#{ name: "オリジナル曲", value: SongTypeValue.Original })
model SongType {
  name: songTypeName;
  value: SongTypeValue;
}

@example(#{
  songId: "3cd42c09-ff3c-4cd2-913f-a279c4ea89b4",
  title: "描き続けた君へ",
  type: #{ name: "オリジナル曲", value: SongTypeValue.Original },
  isDisplay: true,
  orderNo: 1,
})
model SongSummary {
  songId: songId;
  title: title;
  type: SongType;
  isDisplay: boolean;
  orderNo: orderNo;
}

enum SongPersonRole {
  Lyricist: 1,
  Composer: 2,
  Arranger: 3,
}

model SongPerson {
  personId: personId;
  name: personName;
  role: SongPersonRole;
  orderNo: orderNo;
}

model SongAttachedTag {
  songTagId: songTagId;
  name: songTagName;
}
