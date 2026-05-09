namespace IsekaiObservatory.Admin;

model ReleaseGetResponse {
  release: Release;
}

model ReleaseSearchResponse {
  releases: Release[];
  maxPage: int32;
}
