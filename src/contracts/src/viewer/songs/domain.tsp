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

@example(#{ name: "オリジナル", value: SongTypeValue.original })
model SongType {
  name: songTypeName;
  value: SongTypeValue;
}

model SongRelationCounts {
  @doc("関連メディア数")
  mediaCount: int32;
}

@example(#{
  songId: "3cd42c09-ff3c-4cd2-913f-a279c4ea89b4",
  title: "描き続けた君へ",
  description: "楽曲説明",
  type: #{ name: "オリジナル", value: SongTypeValue.original },
  counts: #{ mediaCount: 3 },
})
model SongListItem {
  songId: songId;
  title: title;
  description: description;
  type: SongType;
  counts: SongRelationCounts;
}
