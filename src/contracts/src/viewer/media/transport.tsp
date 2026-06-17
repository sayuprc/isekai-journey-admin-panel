namespace IsekaiObservatory.Viewer;

@example(#{
  media: #[
    #{
      mediaId: "11111111-1111-1111-1111-111111111111",
      title: "描き続けた君へ MV",
      url: "https://example.com/media/1",
      publishedAt: "2024-03-01",
      type: #{ name: "動画", value: MediaTypeValue.video },
      format: #{ name: "MV", value: MediaFormatValue.mv },
      counts: #{ songCount: 1 },
      songs: #[
        #{
          songId: "3cd42c09-ff3c-4cd2-913f-a279c4ea89b4",
          title: "描き続けた君へ",
          type: #{ name: "オリジナル曲", value: SongTypeValue.original },
        }
      ],
    }
  ],
  nextCursor: "eyJwdWJsaXNoZWRBdCI6IjIwMjQtMDMtMDEiLCJtZWRpYUlkIjoiMTExMTExMTEtMTExMS0xMTExLTExMTEtMTExMTExMTExMTExIn0=",
})
model MediaListResponse {
  media: MediaListItem[];

  @doc("続きがある場合のみ返す")
  nextCursor?: cursor;
}
