import "../../shared";

using IsekaiObservatory.Shared;

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
  orderNo: orderNo;
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
  jacketArtUrl: jacketArtUrl | null;
  isDisplay: boolean;
  orderNo: orderNo;

  @minItems(1)
  formatValues: ReleaseFormatValue[];

  media: Medium[];
}

model ReleaseCreateResponse {
  release: Release;
}

model ReleaseJacketArtUploadResponse {
  jacketArtUrl: jacketArtUrl;
}

model ReleaseUpdateRequest {
  name: releaseName;
  releasedOn: releasedOn;
  description: string;
  jacketArtUrl: jacketArtUrl | null;
  isDisplay: boolean;
  orderNo: orderNo;

  @minItems(1)
  formatValues: ReleaseFormatValue[];

  media: Medium[];
}

model ReleaseUpdateResponse {
  release: Release;
}

model ReleaseGetResponse {
  release: Release;

  @doc("所属リリースグループのタイトル。編集対象の文脈表示用")
  releaseGroupTitle: releaseGroupTitle;

  songs: ReleaseReferencedSong[];
}
