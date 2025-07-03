export const Navbar = () => {
  return (
    <nav class="navbar bg-neutral-content text-base-content" aria-label="Site navigation">
      <div class="flex-1">ヰ世界のテラリウム</div>
      <div class="flex-none">
        <ul class="menu menu-horizontal px-1">
          <li><a href="/song-types">楽曲種別一覧</a></li>
          <li><a href="/creators">クリエイター一覧</a></li>
        </ul>
      </div>
    </nav>
  );
};
