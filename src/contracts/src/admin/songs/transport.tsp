namespace IsekaiTerrarium.Admin;

model SongSearchResponse {
  songs: SongSummary[];
  maxPage: int32;
}

model SongGetResponse {
  song: Song;
}

model SongCreateRequest {
  title: title;
  description: description;
  typeValue: SongTypeValue;
  attributeValue?: SongAttributeValue;
  lyricists: RequestLyricist[];
  composers: RequestComposer[];
  arrangers: RequestArranger[];
}

model SongCreateResponse {
  song: Song;
}

model SongUpdateRequest {
  title: title;
  description: description;
  typeValue: SongTypeValue;
  attributeValue?: SongAttributeValue;
  orderNo: orderNo;
  lyricists: RequestLyricist[];
  composers: RequestComposer[];
  arrangers: RequestArranger[];
}

model SongUpdateResponse {
  song: Song;
}

model RequestLyricist is PickProperties<Lyricist, "creatorId">;

model RequestComposer is PickProperties<Composer, "creatorId">;

model RequestArranger is PickProperties<Arranger, "creatorId">;
