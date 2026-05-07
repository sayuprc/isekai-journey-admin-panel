namespace IsekaiObservatory.Viewer;

@doc("楽曲ID")
@format("uuid")
scalar songId extends string;

@doc("楽曲名")
@minLength(1)
scalar title extends string;

@doc("楽曲の説明")
scalar description extends string;

@doc("楽曲種別の表示名")
@minLength(1)
scalar songTypeLabel extends string;

@doc("公開向け楽曲種別")
enum ViewerSongTypeValue {
  original: "original",
  cover: "cover",
}

@example(#{ label: "オリジナル", value: ViewerSongTypeValue.original })
model ViewerSongType {
  label: songTypeLabel;
  value: ViewerSongTypeValue;
}

model SongRelationCounts {
  @doc("関連メディア数")
  mediaCount: int32;
}

@example(#{
  songId: "3cd42c09-ff3c-4cd2-913f-a279c4ea89b4",
  title: "描き続けた君へ",
  type: #{ label: "オリジナル", value: ViewerSongTypeValue.original },
  description: "Viewer の一覧表示向けに集約された楽曲説明",
  counts: #{ mediaCount: 3 },
})
model SongListItem {
  songId: songId;
  title: title;
  type: ViewerSongType;
  description: description;
  counts: SongRelationCounts;
}
