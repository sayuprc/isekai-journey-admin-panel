import "@typespec/http";
import "../../admin/lib/response.tsp";

namespace IsekaiObservatory.Viewer;

@doc("次ページを記すキー")
scalar cursor extends string;

@doc("1 度に取得する件数")
@minValue(1)
@maxValue(50)
scalar limit extends int32;

@example(#{
  songs: #[
    #{
      songId: "3cd42c09-ff3c-4cd2-913f-a279c4ea89b4",
      title: "描き続けた君へ",
      description: "楽曲説明",
      type: #{ name: "オリジナル曲", value: SongTypeValue.original },
      counts: #{
        // releaseCount: 1,
        mediaCount: 1,
      },
      lyricists: #["作詞者"],
      composers: #["作曲者"],
      arrangers: #["編曲者"],
      media: #[
        #{
          mediaId: "11111111-1111-1111-1111-111111111111",
          title: "描き続けた君へ MV",
          type: #{ name: "動画", value: MediaTypeValue.video },
          format: #{ name: "MV", value: MediaFormatValue.mv },
          publishedAt: "2024-03-01",
        }
      ],
    }
  ],
  nextCursor: "eyJvcmRlck5vIjoxLCJzb25nSWQiOiIzY2Q0MmMwOS1mZjNjLTRjZDItOTEzZi1hMjc5YzRlYTg5YjQifQ==",
})
model SongListResponse {
  songs: SongListItem[];

  @doc("続きがある場合のみ返す")
  nextCursor?: cursor;
}
