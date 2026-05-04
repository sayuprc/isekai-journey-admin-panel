import "../lib";

namespace IsekaiObservatory.Admin;

@doc("人物ID")
scalar personId extends uuid;

@doc("人物名")
@minLength(1)
scalar personName extends string;

@example(#{ personId: "0cc5f492-22fe-4f38-b702-8b2375bc0254", name: "ヰ世界情緒", orderNo: 10 })
model Person {
  personId: personId;
  name: personName;
  orderNo: orderNo;
}
