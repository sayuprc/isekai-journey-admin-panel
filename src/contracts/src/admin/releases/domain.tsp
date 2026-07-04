import "../../shared";
import "../../packages/song";
import "../songs";

using IsekaiObservatory.Shared;
using IsekaiObservatory.Packages.Song;

namespace IsekaiObservatory.Admin;

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

alias JacketArtContentType = "image/jpeg" | "image/png" | "image/webp";

@doc("リリースグループ種別")
enum ReleaseGroupTypeValue {
  Single: 1,
  Album: 2,
  Ep: 3,
  Other: 99,
}

@doc("媒体種別")
enum MediumFormatValue {
  Digital: 1,
  Cd: 2,
  Dvd: 3,
  BluRay: 4,
  Other: 99,
}

@doc("リリースグループ（作品）")
model ReleaseGroup {
  releaseGroupId: releaseGroupId;
  title: releaseGroupTitle;
  typeValue: ReleaseGroupTypeValue;
  description: string;
  isDisplay: boolean;
}

@doc("リリースグループ一覧の 1 行分。firstReleasedOn は傘下リリースの最古発売日（リリース未登録なら null）")
model ReleaseGroupSummary {
  releaseGroupId: releaseGroupId;
  title: releaseGroupTitle;
  typeValue: ReleaseGroupTypeValue;
  description: string;
  isDisplay: boolean;
  firstReleasedOn: releasedOn | null;
}

@doc("リリースグループ傘下のリリース 1 件分の概要")
model ReleaseGroupReferencedRelease {
  releaseId: releaseId;
  name: releaseName;
  releasedOn: releasedOn;
  jacketArtUrl: jacketArtUrl | null;
  isDisplay: boolean;
  orderNo: orderNo;
  formatValues: MediumFormatValue[];
}

@doc("収録曲")
model Track {
  songId: uuid;
  trackNo: orderNo;
}

@doc("リリース内の媒体（CD / DVD / 配信 など）")
model Medium {
  position: orderNo;
  formatValue: MediumFormatValue;
  tracks: Track[];
}

@doc("リリース（実際に世に出た版・盤）")
model Release {
  releaseId: releaseId;
  releaseGroupId: releaseGroupId;
  name: releaseName;
  releasedOn: releasedOn;
  description: string;
  jacketArtUrl: jacketArtUrl | null;
  isDisplay: boolean;
  orderNo: orderNo;
  media: Medium[];
}

@doc("リリース収録曲の表示用情報")
model ReleaseReferencedSong {
  mediumPosition: orderNo;
  trackNo: orderNo;
  songId: uuid;
  title: title;
}
