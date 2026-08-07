import { Show, createEffect, createMemo, createSignal, on, onMount } from 'solid-js';
import { kindLabel } from '../../shared/labels';
import { SITE_TITLE } from '../../shared/site';

type DrawerKind = 'song' | 'release' | 'media';

interface DrawerTarget {
  fragmentPath: string;
  id: string;
  kind: DrawerKind;
  pathname: string;
}

const maxCachedFragments = 8;
// 指してすぐには取りにいかない Astro の prefetch (hover) と同じ猶予に揃える
const hoverPrefetchDelay = 80;
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

// 一覧カードのクリックはページ遷移ではなく fragment の取得になるので、先読みも fragment を対象にする
// Save-Data と低速回線では通信を増やさない
const prefetchAllowed = (): boolean => {
  const { connection } = navigator as Navigator & {
    connection?: { saveData?: boolean; effectiveType?: string };
  };

  if (connection === undefined) return true;
  if (connection.saveData === true) return false;

  return connection.effectiveType === undefined || !connection.effectiveType.includes('2g');
};

// ドロワーの各階層を 1 履歴エントリとして積むので、履歴 state には開いている階層のパス一覧を持たせる
const readDrawerPaths = (state: unknown): string[] | null => {
  if (typeof state !== 'object' || state === null) return null;

  const { drawerPaths } = state as { drawerPaths?: unknown };
  if (!Array.isArray(drawerPaths) || drawerPaths.length === 0) return null;
  if (!drawerPaths.every(path => typeof path === 'string')) return null;

  return drawerPaths;
};

const focusableSelector = [
  'a[href]',
  'button:not([disabled])',
  'input:not([disabled])',
  'select:not([disabled])',
  'textarea:not([disabled])',
  '[tabindex]:not([tabindex="-1"])',
].join(', ');

export const DetailDrawer = () => {
  const [stack, setStack] = createSignal<DrawerTarget[]>([]);
  const [content, setContent] = createSignal('');
  const [loading, setLoading] = createSignal(false);
  const [shareLabel, setShareLabel] = createSignal('共有');
  let loadSequence = 0;
  let bodyRef: HTMLDivElement | undefined;
  let panelRef: HTMLElement | undefined;
  let overlayRef: HTMLDivElement | undefined;
  let prefetchTimer = 0;
  let prefetchPath = '';
  // 戻る操作は popstate で完結させるため、応答が返るまで多重発火させない
  let historyNavPending = false;
  let restoreFocusTo: HTMLElement | null = null;
  let inertedElements: Element[] = [];

  const current = createMemo(() => {
    const items = stack();
    return items[items.length - 1] ?? null;
  });

  const isOpen = createMemo(() => current() !== null);

  const loadTarget = async (target: DrawerTarget, commitStack: () => void) => {
    const sequence = loadSequence + 1;
    loadSequence = sequence;

    // 体感遅延を抑えるため fetch の完了を待たずに開き、届くまでスケルトンを見せる
    commitStack();
    setContent('');
    setLoading(true);
    setShareLabel('共有');

    try {
      const html = await fetchTargetFragment(target);
      if (sequence !== loadSequence) return false;

      setContent(html);
      setLoading(false);
      bodyRef?.scrollTo({ top: 0, behavior: 'auto' });
      return true;
    } catch {
      if (sequence === loadSequence) {
        window.location.href = target.pathname;
      }
      return false;
    }
  };

  const openDrawer = async (pathname: string) => {
    const target = resolveDrawerTarget(pathname);
    if (!target) return false;
    if (current()?.pathname === target.pathname) return true;

    return loadTarget(target, () => {
      const nextStack = [...stack(), target];
      setStack(nextStack);
      window.history.pushState(
        { drawerPaths: nextStack.map(item => item.pathname) },
        '',
        target.pathname,
      );
    });
  };

  const closeDrawer = () => {
    loadSequence += 1;
    setStack([]);
    setContent('');
    setLoading(false);
    setShareLabel('共有');
  };

  // × / オーバーレイ / Esc は積んだ階層ぶんまとめて履歴を戻し、実際の閉じ処理は popstate に任せる
  const requestClose = () => {
    const depth = stack().length;
    if (depth === 0 || historyNavPending) return;

    historyNavPending = true;
    window.history.go(-depth);
  };

  const goBack = () => {
    if (!current() || historyNavPending) return;

    historyNavPending = true;
    window.history.back();
  };

  const handlePopState = (event: PopStateEvent) => {
    historyNavPending = false;

    const paths = readDrawerPaths(event.state);
    if (paths === null) {
      if (current()) closeDrawer();
      return;
    }

    const targets = paths.map(resolveDrawerTarget).filter(target => target !== null);
    const nextTarget = targets[targets.length - 1];
    if (targets.length !== paths.length || !nextTarget) {
      closeDrawer();
      return;
    }

    if (current()?.pathname === nextTarget.pathname && stack().length === targets.length) return;

    void loadTarget(nextTarget, () => setStack(targets));
  };

  const handleShare = async () => {
    const target = current();
    if (!target) return;

    const shareUrl = new URL(target.pathname, window.location.origin).toString();
    // 識別子を共有シートに出さないよう、fragment が持つ表示タイトルを使う
    const shareTitle = bodyRef?.querySelector('[data-share-title]')?.getAttribute('data-share-title')
      ?? SITE_TITLE;

    try {
      if (navigator.share) {
        await navigator.share({
          title: shareTitle,
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

  const trapFocus = (event: KeyboardEvent) => {
    if (!panelRef) return;

    const focusables = panelRef.querySelectorAll<HTMLElement>(focusableSelector);
    if (focusables.length === 0) {
      event.preventDefault();
      panelRef.focus();
      return;
    }

    const first = focusables[0];
    const last = focusables[focusables.length - 1];
    const active = document.activeElement;
    const insidePanel = active instanceof Element && panelRef.contains(active);

    if (event.shiftKey) {
      if (!insidePanel || active === first || active === panelRef) {
        event.preventDefault();
        last.focus();
      }
      return;
    }

    if (!insidePanel || active === last) {
      event.preventDefault();
      first.focus();
    }
  };

  const handleKeyDown = (event: KeyboardEvent) => {
    if (!current()) return;

    if (event.key === 'Escape') {
      requestClose();
      return;
    }

    if (event.key === 'Tab') {
      trapFocus(event);
    }
  };

  // 一覧の上を横切っただけのカードは、次のカードに入った時点で予約が取り消されるので取得しない
  // 取得済み / 取得中なら fetchTargetFragment 側のキャッシュで即返るので、同じリンクを何度指しても無害
  const handlePrefetch = (event: Event) => {
    if (!prefetchAllowed()) return;

    const target = resolveAnchorTarget(event.target);
    // 同じリンクの中での移動では取り直さない
    if (target !== null && target.fragmentPath === prefetchPath) return;

    window.clearTimeout(prefetchTimer);
    prefetchPath = target?.fragmentPath ?? '';

    if (target === null) return;

    prefetchTimer = window.setTimeout(() => {
      void fetchTargetFragment(target).catch(() => {});
    }, hoverPrefetchDelay);
  };

  // タップは指が触れた時点で開く意思が確定しているので、猶予なしで即取得する
  const handleTouchPrefetch = (event: Event) => {
    if (!prefetchAllowed()) return;

    const target = resolveAnchorTarget(event.target);
    if (target === null) return;

    window.clearTimeout(prefetchTimer);
    prefetchPath = target.fragmentPath;
    void fetchTargetFragment(target).catch(() => {});
  };

  // 開閉に合わせて背面を操作不能にする (スクロールロック + inert + フォーカスの移動と復帰)
  createEffect(on(isOpen, (open) => {
    if (open) {
      restoreFocusTo = document.activeElement instanceof HTMLElement ? document.activeElement : null;
      document.body.classList.add('drawer-lock');

      for (const child of document.body.children) {
        if (overlayRef !== undefined && child.contains(overlayRef)) continue;
        if (child.hasAttribute('inert')) continue;
        child.setAttribute('inert', '');
        inertedElements.push(child);
      }

      panelRef?.focus();
      return;
    }

    document.body.classList.remove('drawer-lock');
    for (const element of inertedElements) {
      element.removeAttribute('inert');
    }
    inertedElements = [];

    if (restoreFocusTo?.isConnected) {
      restoreFocusTo.focus();
    }
    restoreFocusTo = null;
  }, { defer: true }));

  onMount(() => {
    // リロード直後は前回セッションのドロワー state が残っていることがあるので捨てる
    if (readDrawerPaths(window.history.state) !== null) {
      window.history.replaceState(null, '');
    }

    document.addEventListener('click', handleDocumentClick);
    document.addEventListener('pointerover', handlePrefetch);
    document.addEventListener('focusin', handlePrefetch);
    document.addEventListener('touchstart', handleTouchPrefetch, { passive: true });
    window.addEventListener('keydown', handleKeyDown);
    window.addEventListener('popstate', handlePopState);

    return () => {
      document.removeEventListener('click', handleDocumentClick);
      document.removeEventListener('pointerover', handlePrefetch);
      document.removeEventListener('focusin', handlePrefetch);
      document.removeEventListener('touchstart', handleTouchPrefetch);
      window.removeEventListener('keydown', handleKeyDown);
      window.removeEventListener('popstate', handlePopState);
      window.clearTimeout(prefetchTimer);
    };
  });

  return (
    <Show when={current()}>
      {target => (
        <div class="detail-overlay" ref={el => overlayRef = el} onClick={requestClose}>
          <aside
            class="detail-panel"
            role="dialog"
            aria-modal="true"
            aria-label={`${kindLabel(target().kind)}の詳細`}
            tabindex="-1"
            ref={el => panelRef = el}
            onClick={event => event.stopPropagation()}
          >
            <div class="detail-head">
              <div style={{ 'display': 'flex', 'align-items': 'center', 'gap': '12px' }}>
                <Show when={stack().length > 1}>
                  <button class="icon-btn" type="button" onClick={goBack} title="戻る" aria-label="戻る">
                    <span aria-hidden="true">←</span>
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
                  aria-label="この詳細ページを開く"
                  data-drawer-bypass="true"
                >
                  <span aria-hidden="true">↗</span>
                </a>
                <button class="icon-btn" type="button" onClick={requestClose} title="閉じる" aria-label="閉じる">
                  <span aria-hidden="true">×</span>
                </button>
              </div>
            </div>
            <div class="detail-body" aria-busy={loading()} ref={el => bodyRef = el}>
              <Show when={loading()}>
                <div class="drawer-skeleton" aria-hidden="true">
                  <div class="drawer-skeleton-line drawer-skeleton-eyebrow"></div>
                  <div class="drawer-skeleton-line drawer-skeleton-title"></div>
                  <div class="drawer-skeleton-art"></div>
                  <div class="drawer-skeleton-line"></div>
                  <div class="drawer-skeleton-line"></div>
                  <div class="drawer-skeleton-line drawer-skeleton-short"></div>
                </div>
              </Show>
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
