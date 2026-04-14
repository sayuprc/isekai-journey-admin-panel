namespace IsekaiTerrarium.Admin;

model PerformerListResponse {
  performers: Performer[];
}

model PerformerSearchResponse {
  performers: Performer[];
  maxPage: int32;
}

model PerformerGetResponse {
  performer: Performer;
}

model PerformerCreateRequest {
  name: performerName;
}

model PerformerCreateResponse {
  performer: Performer;
}

model PerformerUpdateRequest {
  name: performerName;
  orderNo: orderNo;
}

model PerformerUpdateResponse {
  performer: Performer;
}
