import "../../shared";

using IsekaiObservatory.Shared;

namespace IsekaiObservatory.Admin;

@doc("場所ID")
scalar placeId extends uuid;

@doc("場所名")
@minLength(1)
scalar placeName extends string;

@doc("場所区分名")
@minLength(1)
scalar placeKindName extends string;

@doc("場所区分の値")
@friendlyName("PlaceKindValue")
enum PlaceKindValue {
  Physical: 1,
  Online: 2,
}

@example(#{ name: "会場", value: PlaceKindValue.Physical })
@friendlyName("PlaceKind")
model PlaceKind {
  name: placeKindName;
  value: PlaceKindValue;
}

@example(#{
  placeId: "0cc5f492-22fe-4f38-b702-8b2375bc0254",
  name: "Zepp Haneda",
  kind: #{ name: "会場", value: PlaceKindValue.Physical },
})
model Place {
  placeId: placeId;
  name: placeName;
  kind: PlaceKind;
}
