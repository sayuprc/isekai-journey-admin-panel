import "../lib";

namespace IsekaiObservatory.Admin;

@doc("リリースID")
scalar releaseId extends uuid;

@doc("リリースタイトル")
@minLength(1)
scalar releaseTitle extends string;

@doc("発売日")
@format("date")
scalar releasedOn extends string;

enum ReleaseTypeValue {
  Single: 1,
  Album: 2,
  Ep: 3,
  Other: 99,
}

enum ReleaseDistributionTypeValue {
  Digital: 1,
  Physical: 2,
  Other: 99,
}

model TrackEntry {
  songId: uuid;
  trackNo: orderNo;
}

model Release {
  releaseId: releaseId;
  title: releaseTitle;
  typeValue: ReleaseTypeValue;
  distributionTypeValue: ReleaseDistributionTypeValue;
  releasedOn: releasedOn;
  description: string;
  isDisplay: boolean;
  trackEntries: TrackEntry[];
}
