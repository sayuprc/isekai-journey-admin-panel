import "../lib";
import "../creators";

namespace IsekaiTerrarium.Admin;

@doc("楽曲ID")
scalar songId extends uuid;

@doc("楽曲名")
@minLength(1)
scalar title extends string;

@doc("楽曲の説明")
scalar description extends string;

@example(#{
  songId: "3cd42c09-ff3c-4cd2-913f-a279c4ea89b4",
  title: "描き続けた君へ",
  description: "オリジナル楽曲",
  type: #{ name: "オリジナル曲", value: SongTypeValue.Original },
  attribute: #{ name: "コラボ", value: SongAttributeValue.Collaboration },
  orderNo: 1,
  lyricists: #[#{ creatorId: "a4d01e9d-e593-4bd9-ba83-e9a7dd3887cd", name: "ヰ世界情緒", orderNo: 1 }],
  composers: #[#{ creatorId: "a4d01e9d-e593-4bd9-ba83-e9a7dd3887cd", name: "ヰ世界情緒", orderNo: 1 }],
  arrangers: #[#{ creatorId: "a4d01e9d-e593-4bd9-ba83-e9a7dd3887cd", name: "ヰ世界情緒", orderNo: 1 }],
})
model Song {
  songId: songId;
  title: title;
  description: description;
  type: SongType;
  attribute?: SongAttribute;
  orderNo: orderNo;
  lyricists: Lyricist[];
  composers: Composer[];
  arrangers: Arranger[];
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

@doc("楽曲属性名")
@minLength(1)
scalar songAttributeName extends string;

@doc("楽曲属性の値")
enum SongAttributeValue {
  Collaboration: 1,
  Lineage: 2,
  Derivative: 3,
  Amplified: 4,
  Isotope: 5,
}

@example(#{ name: "コラボ", value: SongAttributeValue.Collaboration })
model SongAttribute {
  name: songAttributeName;
  value: SongAttributeValue;
}

@example(#{
  songId: "3cd42c09-ff3c-4cd2-913f-a279c4ea89b4",
  title: "描き続けた君へ",
  type: #{ name: "オリジナル曲", value: SongTypeValue.Original },
  attribute: #{ name: "コラボ", value: SongAttributeValue.Collaboration },
  orderNo: 1,
})
model SongSummary {
  songId: songId;
  title: title;
  type: SongType;
  attribute?: SongAttribute;
  orderNo: orderNo;
}

model SongCreator {
  creatorId: creatorId;
  name: creatorName;
  orderNo: orderNo;
}

@example(#{ creatorId: "a4d01e9d-e593-4bd9-ba83-e9a7dd3887cd", name: "ヰ世界情緒", orderNo: 1 })
model Lyricist extends SongCreator {}

@example(#{ creatorId: "a4d01e9d-e593-4bd9-ba83-e9a7dd3887cd", name: "ヰ世界情緒", orderNo: 1 })
model Composer extends SongCreator {}

@example(#{ creatorId: "a4d01e9d-e593-4bd9-ba83-e9a7dd3887cd", name: "ヰ世界情緒", orderNo: 1 })
model Arranger extends SongCreator {}
