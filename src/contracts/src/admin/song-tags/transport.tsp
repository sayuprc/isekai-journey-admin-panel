namespace IsekaiObservatory.Admin;

model SongTagListResponse {
  tags: SongTag[];
}

model SongTagSearchResponse {
  tags: SongTag[];
  maxPage: int32;
}
