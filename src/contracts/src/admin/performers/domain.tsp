import "../lib";

namespace IsekaiTerrarium.Admin;

@doc("共演者ID")
scalar performerId extends uuid;

@doc("共演者名")
@minLength(1)
scalar performerName extends string;

@example(#{ performerId: "3cd42c09-ff3c-4cd2-913f-a279c4ea89b4", name: "ヰ世界情緒", orderNo: 1 })
model Performer {
  performerId: performerId;
  name: performerName;
  orderNo: orderNo;
}
