const navItems = [
  {
    title: '楽曲種別',
    links: [
      { href: '/song-types', label: '一覧' },
    ],
  },
  {
    title: '楽曲',
    links: [
      { href: '/songs/create', label: '作成' },
    ],
  },
  {
    title: 'クリエイター',
    links: [
      { href: '/creators', label: '一覧' },
      { href: '/creators/create', label: '作成' },
    ],
  },
  {
    title: '共演者',
    links: [
      { href: '/performers', label: '一覧' },
      { href: '/performers/create', label: '作成' },
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
          <h2>{item.title}</h2>
          <ul class="menu">
            {item.links.map(link => (
              <li><a href={link.href}>{link.label}</a></li>
            ))}
          </ul>
        </>
      ))}
    </aside>
  );
};
