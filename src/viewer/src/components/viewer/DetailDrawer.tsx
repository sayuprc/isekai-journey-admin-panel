import { Show, createMemo, createSignal, onMount } from 'solid-js';
import { kindLabel } from '../../shared/labels';

type DrawerKind = 'song' | 'release' | 'media';

interface DrawerTarget {
  fragmentPath: string;
  id: string;
  kind: DrawerKind;
  pathname: string;
}

const maxCachedFragments = 8;
const fragmentHtmlCache = new Map<string, string>();
const fragmentRequestCache = new Map<string, Promise<string>>();

const resolveDrawerTarget = (pathname: string): DrawerTarget | null => {
  const patterns = [
    { fragment: '/fragments/songs/', kind: 'song' as const, prefix: '/songs/' },
    { fragment: '/fragments/releases/', kind: 'release' as const, prefix: '/releases/' },
    { fragment: '/fragments/media/', kind: 'media' as const, prefix: '/media/' },
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

const resolveAnchorTarget = (eventTarget: EventTarget | null): DrawerTarget | null => {
  if (!(eventTarget instanceof Element)) return null;

  const anchor = eventTarget.closest('a[href]');
  if (!(anchor instanceof HTMLAnchorElement)) return null;
  if (anchor.dataset.drawerBypass === 'true') return null;
  if (anchor.target && anchor.target !== '_self') return null;

  const url = new URL(anchor.href, window.location.origin);
  if (url.origin !== window.location.origin) return null;

  return resolveDrawerTarget(url.pathname);
};

const fetchTargetFragment = (target: DrawerTarget): Promise<string> => {
  const cachedHtml = fragmentHtmlCache.get(target.fragmentPath);
  if (cachedHtml !== undefined) {
    fragmentHtmlCache.delete(target.fragmentPath);
    fragmentHtmlCache.set(target.fragmentPath, cachedHtml);
    return Promise.resolve(cachedHtml);
  }

  const cachedRequest = fragmentRequestCache.get(target.fragmentPath);
  if (cachedRequest) return cachedRequest;

  const request = fetch(target.fragmentPath)
    .then((response) => {
      if (!response.ok) {
        throw new Error(`Failed to fetch ${target.fragmentPath}: ${response.status}`);
      }

      return response.text();
    })
    .then((html) => {
      fragmentHtmlCache.set(target.fragmentPath, html);
      if (fragmentHtmlCache.size > maxCachedFragments) {
        const oldestKey = fragmentHtmlCache.keys().next().value;
        if (oldestKey !== undefined) {
          fragmentHtmlCache.delete(oldestKey);
        }
      }
      fragmentRequestCache.delete(target.fragmentPath);
      return html;
    })
    .catch((error: unknown) => {
      fragmentRequestCache.delete(target.fragmentPath);
      throw error;
    });

  fragmentRequestCache.set(target.fragmentPath, request);
  return request;
};

export const DetailDrawer = () => {
  const [stack, setStack] = createSignal<DrawerTarget[]>([]);
  const [content, setContent] = createSignal('');
  const [isLoading, setIsLoading] = createSignal(false);
  const [shareLabel, setShareLabel] = createSignal('共有');
  let loadSequence = 0;
  let bodyRef: HTMLDivElement | undefined;

  const current = createMemo(() => {
    const items = stack();
    return items[items.length - 1] ?? null;
  });

  const loadTarget = async (target: DrawerTarget, commitStack: () => void) => {
    const sequence = loadSequence + 1;
    loadSequence = sequence;
    setIsLoading(true);

    try {
      const html = await fetchTargetFragment(target);
      if (sequence !== loadSequence) return false;

      commitStack();
      setContent(html);
      bodyRef?.scrollTo({ top: 0, behavior: 'auto' });
      setShareLabel('共有');
      return true;
    } catch {
      if (sequence === loadSequence) {
        window.location.href = target.pathname;
      }
      return false;
    } finally {
      if (sequence === loadSequence) {
        setIsLoading(false);
      }
    }
  };

  const openDrawer = async (pathname: string) => {
    const target = resolveDrawerTarget(pathname);
    if (!target) return false;
    if (current()?.pathname === target.pathname) return true;

    return loadTarget(target, () => setStack((prev) => {
      const last = prev[prev.length - 1];
      if (last?.pathname === target.pathname) return prev;
      return [...prev, target];
    }));
  };

  const closeDrawer = () => {
    loadSequence += 1;
    setStack([]);
    setContent('');
    setIsLoading(false);
    setShareLabel('共有');
  };

  const goBack = async () => {
    const prev = stack();
    if (prev.length <= 1) {
      closeDrawer();
      return;
    }

    const nextStack = prev.slice(0, -1);
    const target = nextStack[nextStack.length - 1];
    await loadTarget(target, () => setStack(nextStack));
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

    const drawerTarget = resolveAnchorTarget(event.target);
    if (!drawerTarget) return;

    event.preventDefault();
    void openDrawer(drawerTarget.pathname);
  };

  const prefetchDrawerTarget = (eventTarget: EventTarget | null) => {
    const target = resolveAnchorTarget(eventTarget);
    if (!target) return;

    void fetchTargetFragment(target).catch(() => undefined);
  };

  const handleDocumentPointerOver = (event: PointerEvent) => {
    if (event.pointerType === 'touch') return;
    prefetchDrawerTarget(event.target);
  };

  const handleDocumentFocusIn = (event: FocusEvent) => {
    prefetchDrawerTarget(event.target);
  };

  const handleKeyDown = (event: KeyboardEvent) => {
    if (event.key === 'Escape' && current()) {
      closeDrawer();
    }
  };

  onMount(() => {
    document.addEventListener('click', handleDocumentClick);
    document.addEventListener('pointerover', handleDocumentPointerOver);
    document.addEventListener('focusin', handleDocumentFocusIn);
    window.addEventListener('keydown', handleKeyDown);

    return () => {
      document.removeEventListener('click', handleDocumentClick);
      document.removeEventListener('pointerover', handleDocumentPointerOver);
      document.removeEventListener('focusin', handleDocumentFocusIn);
      window.removeEventListener('keydown', handleKeyDown);
    };
  });

  return (
    <Show when={current()}>
      {target => (
        <div class="detail-overlay" onClick={closeDrawer}>
          <aside class="detail-panel" onClick={event => event.stopPropagation()}>
            <div class="detail-head">
              <div style={{ 'display': 'flex', 'align-items': 'center', 'gap': '12px' }}>
                <Show when={stack().length > 1}>
                  <button class="icon-btn" type="button" onClick={() => void goBack()} title="戻る">
                    ←
                  </button>
                </Show>
                <div class="label-mono">{kindLabel(target().kind)}</div>
              </div>
              <div class="drawer-head-actions">
                <Show when={stack().length > 1}>
                  <div class="label-mono" style={{ color: 'var(--fg-faint)' }}>
                    {stack().length} 階層
                  </div>
                </Show>
                <button class="icon-btn drawer-share-btn" type="button" onClick={handleShare} title="共有リンク">
                  {shareLabel()}
                </button>
                <a
                  class="icon-btn drawer-open-link"
                  href={target().pathname}
                  title="この詳細ページを開く"
                  data-drawer-bypass="true"
                >
                  ↗
                </a>
                <button class="icon-btn" type="button" onClick={closeDrawer} title="閉じる">
                  ×
                </button>
              </div>
            </div>
            <div class={`detail-body ${isLoading() ? 'detail-body-loading' : ''}`} aria-busy={isLoading()} ref={bodyRef}>
              <Show when={content()}>
                <div innerHTML={content()}></div>
              </Show>
            </div>
          </aside>
        </div>
      )}
    </Show>
  );
};
