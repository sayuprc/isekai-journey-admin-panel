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
    }
  ],
  nextCursor: "eyJwdWJsaXNoZWRBdCI6IjIwMjQtMDMtMDEiLCJtZWRpYUlkIjoiMTExMTExMTEtMTExMS0xMTExLTExMTEtMTExMTExMTExMTExIn0=",
})
model MediaListResponse {
  media: MediaListItem[];

  @doc("続きがある場合のみ返す")
  nextCursor?: cursor;
}
