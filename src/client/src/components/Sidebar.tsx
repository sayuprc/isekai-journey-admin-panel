export const Sidebar = () => {
  return (
    <aside class="w-48 bg-base-200 min-h-screen p-4">
      <div class="mb-6">
        <span class="text-xl font-bold">ヰ世界のテラリウム</span>
      </div>
      <h2>楽曲種別</h2>
      <ul class="menu">
        <li><a href="/song-types">一覧</a></li>
        <li><a href="/song-types/create">作成</a></li>
      </ul>
      <h2>クリエイター</h2>
      <ul class="menu">
        <li><a href="/creators">一覧</a></li>
      </ul>
    </aside>
  );
};
