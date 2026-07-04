import "../../shared";

using IsekaiObservatory.Shared;

namespace IsekaiObservatory.Viewer;

@doc("リリースグループID")
scalar releaseGroupId extends uuid;

@doc("リリースグループタイトル")
@minLength(1)
scalar releaseGroupTitle extends string;

@doc("リリースID")
scalar releaseId extends uuid;

@doc("リリース版名")
@minLength(1)
scalar releaseName extends string;

@doc("発売日")
@format("date")
scalar releasedOn extends string;

@doc("ジャケットアートURL")
scalar jacketArtUrl extends string;

@doc("リリースグループ種別の値")
enum ReleaseGroupTypeValue {
  Single: 1,
  Album: 2,
  Ep: 3,
  Other: 99,
}

@example(#{ name: "アルバム", value: ReleaseGroupTypeValue.Album })
model ReleaseGroupType {
  name: string;
  value: ReleaseGroupTypeValue;
}

@doc("媒体種別の値")
enum MediumFormatValue {
  Digital: 1,
  Cd: 2,
  Dvd: 3,
  BluRay: 4,
  Other: 99,
}

@example(#{ name: "CD", value: MediumFormatValue.Cd })
model MediumFormat {
  name: string;
  value: MediumFormatValue;
}

@doc("収録曲")
model ReleaseTrackItem {
  trackNo: orderNo;
  songId: uuid;
  title: string;
  isDisplay: boolean;
}

@doc("リリース内の媒体")
model ReleaseMediumItem {
  position: orderNo;
  format: MediumFormat;
  tracks: ReleaseTrackItem[];
}

@doc("リリース（版）")
model ReleaseListItem {
  releaseId: releaseId;
  name: releaseName;
  releasedOn: releasedOn;
  description: string;
  jacketArtUrl: jacketArtUrl | null;
  media: ReleaseMediumItem[];
}

@doc("リリースグループ。公開リリースを 1 件以上持つものだけが一覧に載る")
model ReleaseGroupListItem {
  releaseGroupId: releaseGroupId;
  title: releaseGroupTitle;
  type: ReleaseGroupType;
  description: string;

  @doc("傘下の公開リリースの最古発売日")
  firstReleasedOn: releasedOn;

  releases: ReleaseListItem[];
}
