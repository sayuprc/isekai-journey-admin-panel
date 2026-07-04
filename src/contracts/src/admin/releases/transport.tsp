namespace IsekaiObservatory.Admin;

model ReleaseGroupCreateRequest {
  title: releaseGroupTitle;
  typeValue: ReleaseGroupTypeValue;
  description: string;
  isDisplay: boolean;
}

model ReleaseGroupCreateResponse {
  releaseGroup: ReleaseGroup;
}

model ReleaseGroupUpdateRequest {
  title: releaseGroupTitle;
  typeValue: ReleaseGroupTypeValue;
  description: string;
  isDisplay: boolean;
}

model ReleaseGroupUpdateResponse {
  releaseGroup: ReleaseGroup;
}

model ReleaseGroupGetResponse {
  releaseGroup: ReleaseGroup;
  releases: ReleaseGroupReferencedRelease[];
}

model ReleaseGroupSearchResponse {
  releaseGroups: ReleaseGroupSummary[];
  maxPage: int32;
}

model ReleaseCreateRequest {
  releaseGroupId: releaseGroupId;
  name: releaseName;
  releasedOn: releasedOn;
  description: string;
  isDisplay: boolean;
  media: Medium[];
}

model ReleaseCreateResponse {
  release: Release;
}

model ReleaseUpdateRequest {
  name: releaseName;
  releasedOn: releasedOn;
  description: string;
  isDisplay: boolean;
  media: Medium[];
}

model ReleaseUpdateResponse {
  release: Release;
}

model ReleaseGetResponse {
  release: Release;
  songs: ReleaseReferencedSong[];
}
