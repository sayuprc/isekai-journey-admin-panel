namespace IsekaiObservatory.Viewer;

@example(#{ songCount: 42 })
model SiteStatsResponse {
  @doc("公開対象楽曲数")
  songCount: int32;
}
