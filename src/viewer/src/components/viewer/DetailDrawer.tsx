import { Show, createSignal, onMount } from 'solid-js';
import { kindLabel } from '../../data/site-helpers';

interface DrawerTarget {
  fragmentPath: string;
  id: string;
  kind: 'song' | 'release' | 'media' | 'event';
  pathname: string;
}

const resolveDrawerTarget = (pathname: string): DrawerTarget | null => {
  const patterns = [
    {
      fragment: '/fragments/songs/',
      kind: 'song' as const,
      prefix: '/songs/',
    },
    {
      fragment: '/fragments/releases/',
      kind: 'release' as const,
      prefix: '/releases/',
    },
    {
      fragment: '/fragments/media/',
      kind: 'media' as const,
      prefix: '/media/',
    },
    {
      fragment: '/fragments/events/',
      kind: 'event' as const,
      prefix: '/events/',
    },
  ];

  for (const pattern of patterns) {
    if (!pathname.startsWith(pattern.prefix)) continue;
    const id = pathname.slice(pattern.prefix.length).split('/')[0];
    if (!id) return null;
    return {
      fragmentPath: `${pattern.fragment}${id}/`,
      id,
      kind: pattern.kind,
      pathname: `${pattern.prefix}${id}`,
    };
  }

  return null;
};

export const DetailDrawer = () => {
  const [content, setContent] = createSignal('');
  const [current, setCurrent] = createSignal<DrawerTarget | null>(null);
  const [isLoading, setIsLoading] = createSignal(false);
  const [shareLabel, setShareLabel] = createSignal('共有');
  let bodyRef: HTMLDivElement | undefined;

  const loadTarget = async (target: DrawerTarget) => {
    setCurrent(target);
    setIsLoading(true);

    try {
      const response = await fetch(target.fragmentPath);
      if (!response.ok) {
        window.location.href = target.pathname;
        return;
      }
      const html = await response.text();
      setContent(html);
      bodyRef?.scrollTo({ top: 0, behavior: 'auto' });
    } catch {
      window.location.href = target.pathname;
    } finally {
      setIsLoading(false);
    }
  };

  const openDrawer = async (pathname: string) => {
    const target = resolveDrawerTarget(pathname);
    if (!target) return false;

    await loadTarget(target);
    return true;
  };

  const closeDrawer = () => {
    setCurrent(null);
    setContent('');
    setIsLoading(false);
    setShareLabel('共有');
  };

  const handleShare = async () => {
    const target = current();
    if (!target) return;

    const shareUrl = new URL(target.pathname, window.location.origin).toString();

    try {
      if (navigator.share) {
        await navigator.share({
          title: target.id.toUpperCase(),
          url: shareUrl,
        });
        return;
      }

      await navigator.clipboard.writeText(shareUrl);
      setShareLabel('コピー済み');
      window.setTimeout(() => setShareLabel('共有'), 1200);
    } catch {
      window.open(shareUrl, '_blank', 'noopener,noreferrer');
    }
  };

  const handleDocumentClick = (event: MouseEvent) => {
    if (event.defaultPrevented) return;
    if (event.button !== 0) return;
    if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;

    const target = event.target;
    if (!(target instanceof Element)) return;

    const anchor = target.closest('a[href]');
    if (!(anchor instanceof HTMLAnchorElement)) return;
    if (anchor.dataset.drawerBypass === 'true') return;
    if (anchor.target && anchor.target !== '_self') return;

    const url = new URL(anchor.href, window.location.origin);
    if (url.origin !== window.location.origin) return;

    const drawerTarget = resolveDrawerTarget(url.pathname);
    if (!drawerTarget) return;

    event.preventDefault();
    void openDrawer(url.pathname);
  };

  const handleKeyDown = (event: KeyboardEvent) => {
    if (event.key === 'Escape' && current()) {
      closeDrawer();
    }
  };

  onMount(() => {
    document.addEventListener('click', handleDocumentClick);
    window.addEventListener('keydown', handleKeyDown);

    return () => {
      document.removeEventListener('click', handleDocumentClick);
      window.removeEventListener('keydown', handleKeyDown);
    };
  });

  return (
    <Show when={current()}>
      {target => (
        <div class="detail-overlay" onClick={closeDrawer}>
          <aside class="detail-panel" onClick={event => event.stopPropagation()}>
            <div class="detail-head">
              <div class="label-mono">
                {kindLabel(target().kind)} · {target().id.toUpperCase()}
              </div>
              <div class="drawer-head-actions">
                <button class="icon-btn drawer-share-btn" type="button" onClick={handleShare} title="共有リンク">
                  {shareLabel()}
                </button>
                <a class="icon-btn drawer-open-link" href={target().pathname} title="この詳細ページを開く" data-drawer-bypass="true">
                  ↗
                </a>
                <button class="icon-btn" type="button" onClick={closeDrawer} title="閉じる">
                  ×
                </button>
              </div>
            </div>
            <div class={`detail-body ${isLoading() ? 'detail-body-loading' : ''}`} ref={bodyRef}>
              <Show when={content()} fallback={<div class="drawer-loading">Loading…</div>}>
                <div innerHTML={content()}></div>
              </Show>
              <Show when={isLoading() && content()}>
                <div class="drawer-loading-overlay">Loading…</div>
              </Show>
            </div>
          </aside>
        </div>
      )}
    </Show>
  );
};
