import "../lib";

namespace IsekaiObservatory.Admin;

@doc("楽曲タグID")
scalar songTagId extends uuid;

@doc("楽曲タグ名")
@minLength(1)
scalar songTagName extends string;

@example(#{ songTagId: "0cc5f492-22fe-4f38-b702-8b2375bc0254", name: "ライブ定番", orderNo: 10 })
model SongTag {
  songTagId: songTagId;
  name: songTagName;
  orderNo: orderNo;
}
