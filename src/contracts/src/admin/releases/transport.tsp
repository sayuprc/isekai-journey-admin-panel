namespace IsekaiObservatory.Admin;

model ReleaseCreateRequest {
  title: releaseTitle;
  typeValue: ReleaseTypeValue;
  distributionTypeValue: ReleaseDistributionTypeValue;
  releasedOn: releasedOn;
  description?: string;
  isDisplay: boolean;
  trackEntries: TrackEntry[];
}

model ReleaseCreateResponse {
  release: Release;
}

model ReleaseUpdateRequest {
  title: releaseTitle;
  typeValue: ReleaseTypeValue;
  distributionTypeValue: ReleaseDistributionTypeValue;
  releasedOn: releasedOn;
  description: string;
  isDisplay: boolean;
  trackEntries: TrackEntry[];
}

model ReleaseUpdateResponse {
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
