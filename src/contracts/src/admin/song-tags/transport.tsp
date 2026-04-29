namespace IsekaiObservatory.Admin;

model SongTagListResponse {
  tags: SongTag[];
}

model SongTagSearchResponse {
  tags: SongTag[];
  maxPage: int32;
}

model SongTagCreateRequest {
  name: songTagName;
}

model SongTagCreateResponse {
  tag: SongTag;
}

model SongTagUpdateRequest {
  name: songTagName;
  orderNo: orderNo;
}

model SongTagUpdateResponse {
  tag: SongTag;
}
