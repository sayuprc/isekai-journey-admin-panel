namespace IsekaiObservatory.Admin;

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
  lyricsLink: lyricsLink | null;
  typeValue: SongTypeValue;
  isDisplay: boolean;
  lyricists: RequestLyricist[];
  composers: RequestComposer[];
  arrangers: RequestArranger[];
  tags: RequestSongTag[];
}

model SongCreateResponse {
  song: Song;
}

model SongUpdateRequest {
  title: title;
  description: description;
  lyricsLink: lyricsLink | null;
  typeValue: SongTypeValue;
  isDisplay: boolean;
  orderNo: orderNo;
  lyricists: RequestLyricist[];
  composers: RequestComposer[];
  arrangers: RequestArranger[];
  tags: RequestSongTag[];
}

model SongUpdateResponse {
  song: Song;
}

model RequestLyricist is PickProperties<Lyricist, "creatorId">;

model RequestComposer is PickProperties<Composer, "creatorId">;

model RequestArranger is PickProperties<Arranger, "creatorId">;

model RequestSongTag is PickProperties<SongTag, "songTagId">;
