namespace IsekaiObservatory.Admin;

model ReleaseCreateRequest {
  title: releaseTitle;
  typeValue: ReleaseTypeValue;
  distributionTypeValue: ReleaseDistributionTypeValue;
  releasedOn: releasedOn;
  description: string;
  isDisplay: boolean;
}

model ReleaseCreateResponse {
  release: Release;
}

model ReleaseGetResponse {
  release: Release;
  songs: ReleaseReferencedSong[];
}

model ReleaseSearchResponse {
  releases: Release[];
  maxPage: int32;
}
