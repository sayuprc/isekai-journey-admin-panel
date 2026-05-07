import "@typespec/http";
import "../../admin/lib/response.tsp";

namespace IsekaiObservatory.Viewer;

@doc("次ページを記すキー")
scalar cursor extends string;

@doc("1 度に取得できる最大件数")
@minValue(1)
@maxValue(500)
scalar limit extends int32;

@example(#{
  songs: #[
    #{
      songId: "3cd42c09-ff3c-4cd2-913f-a279c4ea89b4",
      title: "描き続けた君へ",
      description: "楽曲説明",
      type: #{ name: "オリジナル", value: SongTypeValue.original },
      counts: #{ mediaCount: 3 },
    }
  ],
  nextCursor: "ffc3cb4d-116e-4731-9d90-f01c33253e0c",
})
model SongListResponse {
  songs: SongListItem[];

  @doc("続きを取得できる場合のみ返す")
  nextCursor?: cursor;
}
