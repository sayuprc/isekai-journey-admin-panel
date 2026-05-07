import "@typespec/http";
import "../../admin/lib/response.tsp";

namespace IsekaiObservatory.Viewer;

@doc("シーク法の続きを示す不透明 cursor。将来の分割取得で安定キーをもとに発行する想定。")
scalar cursor extends string;

@doc("分割取得が必要になった場合の取得件数。省略時は SSG build 用の全件取得として扱う。")
@minValue(1)
@maxValue(500)
scalar limit extends int32;

@doc("Viewer の楽曲一覧レスポンス。一括取得を基本としつつ、将来の seek pagination 追加時も後方互換で拡張できる envelope にしている。")
@example(#{
  songs: #[
    #{
      songId: "3cd42c09-ff3c-4cd2-913f-a279c4ea89b4",
      title: "描き続けた君へ",
      type: #{ label: "オリジナル", value: ViewerSongTypeValue.original },
      description: "Viewer の一覧表示向けに集約された楽曲説明",
      counts: #{ mediaCount: 3 },
    }
  ],
  nextCursor: null,
})
model SongListResponse {
  @doc("公開対象の楽曲一覧。")
  songs: SongListItem[];

  @doc("続きを取得できる場合のみ返す cursor。一括取得では `null` または未設定を想定。")
  nextCursor?: cursor | null;
}
