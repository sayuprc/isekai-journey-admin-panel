const navItems = [
  {
    title: '楽曲管理',
    links: [
      { href: '/songs', label: '楽曲一覧' },
      { href: '/songs/create', label: '楽曲作成' },
      { href: '/song-types', label: '楽曲種別' },
    ],
  },
  {
    title: '人物・関係者',
    links: [
      { href: '/creators', label: 'クリエイター一覧' },
      { href: '/creators/create', label: 'クリエイター作成' },
      { href: '/performers', label: '共演者一覧' },
      { href: '/performers/create', label: '共演者作成' },
    ],
  },
];

export const Sidebar = () => {
  return (
    <aside class="w-48 bg-base-200 min-h-screen p-4">
      <div class="mb-6">
        <span class="text-xl font-bold">ヰ世界のテラリウム</span>
      </div>
      {navItems.map(item => (
        <>
          <h2 class="font-bold mb-2">{item.title}</h2>
          <ul class="menu mb-4">
            {item.links.map(link => (
              <li><a href={link.href}>{link.label}</a></li>
            ))}
          </ul>
        </>
      ))}
    </aside>
  );
};
