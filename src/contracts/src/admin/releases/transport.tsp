namespace IsekaiObservatory.Admin;

model ReleaseSearchResponse {
  releases: Release[];
  maxPage: int32;
}
