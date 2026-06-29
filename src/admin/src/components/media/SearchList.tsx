import { For, Match, Show, Switch, createResource, createSignal } from 'solid-js';
import type { MediaTypeValue } from '../../generated';
import { client } from '../../utils/client';
import { ListState } from '../ListState';

const PER_PAGE_OPTIONS = [25, 50, 100] as const;
type PerPage = (typeof PER_PAGE_OPTIONS)[number];
type DisplayFilter = '' | 'true' | 'false';

const MEDIA_TYPE_OPTIONS: Array<{ value: '' | `${MediaTypeValue}`; label: string }> = [
  { value: '', label: 'すべて' },
  { value: '1', label: 'MV' },
  { value: '2', label: '音源動画' },
  { value: '3', label: '配信' },
  { value: '4', label: 'ショート' },
  { value: '5', label: '投稿' },
  { value: '99', label: 'その他' },
];

const DEFAULT_PARAMS = {
  title: '',
  type: '' as '' | `${MediaTypeValue}`,
  isDisplay: '' as DisplayFilter,
  page: 1,
  perPage: 25 as PerPage,
};

const getInitialParams = () => {
  const params = new URLSearchParams(window.location.search);
  const perPageRaw = Number(params.get('per_page'));

  return {
    title: params.get('title') ?? DEFAULT_PARAMS.title,
    type: (params.get('type') ?? DEFAULT_PARAMS.type) as '' | `${MediaTypeValue}`,
    isDisplay: (params.get('is_display') ?? DEFAULT_PARAMS.isDisplay) as DisplayFilter,
    page: Number(params.get('page') ?? String(DEFAULT_PARAMS.page)) || DEFAULT_PARAMS.page,
    perPage: (PER_PAGE_OPTIONS.includes(perPageRaw as PerPage) ? perPageRaw : DEFAULT_PARAMS.perPage) as PerPage,
  };
};

export const SearchList = () => {
  const normalizeDateDisplayValue = (value: unknown): string => {
    if (value instanceof Date) {
      return Number.isNaN(value.getTime()) ? '' : value.toISOString().slice(0, 10);
    }

    if (typeof value !== 'string') {
      return '';
    }

    if (/^\d{4}-\d{2}-\d{2}$/.test(value)) {
      return value;
    }

    const parsed = new Date(value);

    return Number.isNaN(parsed.getTime()) ? '' : parsed.toISOString().slice(0, 10);
  };

  const initial = getInitialParams();

  const [title, setTitle] = createSignal(initial.title);
  const [type, setType] = createSignal(initial.type);
  const [isDisplay, setIsDisplay] = createSignal<DisplayFilter>(initial.isDisplay);
  const [page, setPage] = createSignal(initial.page);
  const [perPage, setPerPage] = createSignal<PerPage>(initial.perPage);

  const [inputTitle, setInputTitle] = createSignal(initial.title);
  const [inputType, setInputType] = createSignal(initial.type);
  const [inputIsDisplay, setInputIsDisplay] = createSignal<DisplayFilter>(initial.isDisplay);
  const [inputPerPage, setInputPerPage] = createSignal<PerPage>(initial.perPage);

  const updateUrl = (params: {
    title: string;
    type: string;
    isDisplay: DisplayFilter;
    page: number;
    perPage: number;
  }) => {
    const searchParams = new URLSearchParams();
    if (params.title) searchParams.set('title', params.title);
    if (params.type) searchParams.set('type', params.type);
    if (params.isDisplay) searchParams.set('is_display', params.isDisplay);
    searchParams.set('page', String(params.page));
    searchParams.set('per_page', String(params.perPage));
    history.pushState(null, '', `?${searchParams.toString()}`);
  };

  const [fetchError, setFetchError] = createSignal<string | null>(null);

  const [data, { refetch }] = createResource(
    () => ({
      title: title(),
      type: type(),
      isDisplay: isDisplay(),
      page: page(),
      perPage: perPage(),
    }),
    async (params) => {
      setFetchError(null);

      const { data, status } = await client.api.media.search.get({
        query: {
          title: params.title,
          type: params.type || undefined,
          is_display: params.isDisplay === '' ? undefined : params.isDisplay === 'true',
          page: params.page,
          per_page: params.perPage,
        },
      });

      if (status === 401) {
        window.location.href = '/auth/login';
        return;
      }

      if (!data) {
        setFetchError('データの取得に失敗しました。再度お試しください。');
        return;
      }

      return data;
    },
  );

  const handleSearch = (e: Event) => {
    e.preventDefault();

    const newPage = 1;
    setTitle(inputTitle());
    setType(inputType());
    setIsDisplay(inputIsDisplay());
    setPerPage(inputPerPage());
    setPage(newPage);
    updateUrl({
      title: inputTitle(),
      type: inputType(),
      isDisplay: inputIsDisplay(),
      page: newPage,
      perPage: inputPerPage(),
    });
  };

  const handlePageChange = (nextPage: number) => {
    setPage(nextPage);
    updateUrl({
      title: title(),
      type: type(),
      isDisplay: isDisplay(),
      page: nextPage,
      perPage: perPage(),
    });
  };

  const handleReset = () => {
    setInputTitle(DEFAULT_PARAMS.title);
    setInputType(DEFAULT_PARAMS.type);
    setInputIsDisplay(DEFAULT_PARAMS.isDisplay);
    setInputPerPage(DEFAULT_PARAMS.perPage);
    setTitle(DEFAULT_PARAMS.title);
    setType(DEFAULT_PARAMS.type);
    setIsDisplay(DEFAULT_PARAMS.isDisplay);
    setPage(DEFAULT_PARAMS.page);
    setPerPage(DEFAULT_PARAMS.perPage);
    updateUrl(DEFAULT_PARAMS);
  };

  return (
    <>
      <form onSubmit={handleSearch} class="mb-4 flex flex-wrap items-end gap-4">
        <fieldset class="fieldset">
          <label class="fieldset-label" for="title">
            タイトル
          </label>
          <input
            type="text"
            id="title"
            name="title"
            value={inputTitle()}
            onInput={e => setInputTitle(e.currentTarget.value)}
            class="input input-bordered input-sm"
            placeholder="メディアタイトルで検索"
          />
        </fieldset>
        <fieldset class="fieldset">
          <label class="fieldset-label" for="type">
            種別
          </label>
          <select
            id="type"
            name="type"
            class="select select-bordered select-sm"
            onChange={e => setInputType(e.currentTarget.value as '' | `${MediaTypeValue}`)}
          >
            <For each={MEDIA_TYPE_OPTIONS}>
              {option => (
                <option value={option.value} selected={inputType() === option.value}>
                  {option.label}
                </option>
              )}
            </For>
          </select>
        </fieldset>
        <fieldset class="fieldset">
          <label class="fieldset-label" for="isDisplay">
            表示設定
          </label>
          <select
            id="isDisplay"
            name="isDisplay"
            class="select select-bordered select-sm"
            onChange={e => setInputIsDisplay(e.currentTarget.value as DisplayFilter)}
          >
            <option value="" selected={inputIsDisplay() === ''}>
              すべて
            </option>
            <option value="true" selected={inputIsDisplay() === 'true'}>
              表示する
            </option>
            <option value="false" selected={inputIsDisplay() === 'false'}>
              表示しない
            </option>
          </select>
        </fieldset>
        <fieldset class="fieldset">
          <label class="fieldset-label" for="perPage">
            表示件数
          </label>
          <select
            id="perPage"
            name="perPage"
            class="select select-bordered select-sm"
            onChange={e => setInputPerPage(Number(e.currentTarget.value) as PerPage)}
          >
            <For each={PER_PAGE_OPTIONS}>
              {n => (
                <option value={n} selected={inputPerPage() === n}>
                  {n}件
                </option>
              )}
            </For>
          </select>
        </fieldset>
        <button type="submit" class="btn btn-primary btn-sm mb-1">
          検索
        </button>
        <button type="button" class="btn btn-ghost btn-sm mb-1" onClick={handleReset}>
          リセット
        </button>
      </form>

      <div class="mb-4 flex justify-end">
        <a href={`/media/create?back=${encodeURIComponent(window.location.search)}`} class="btn btn-primary btn-sm">
          新規作成
        </a>
      </div>

      <div class="overflow-x-auto rounded-box border border-base-300 bg-base-100">
        <table class="table table-zebra">
          <thead>
            <tr>
              <th>タイトル</th>
              <th>公開日</th>
              <th>種別</th>
              <th>表示設定</th>
              <th>URL</th>
              <th>操作</th>
            </tr>
          </thead>
          <tbody>
            <Switch>
              <Match when={data.loading}>
                <ListState state="loading" colSpan={6} />
              </Match>
              <Match when={fetchError()}>
                {message => <ListState state="error" colSpan={6} message={message()} onRetry={() => refetch()} />}
              </Match>
              <Match when={data() && data()!.media.length === 0}>
                <ListState state="empty" colSpan={6} message="条件に一致するメディアはありません。" />
              </Match>
              <Match when={data()}>
                {result => (
                  <For each={result().media}>
                    {media => (
                      <tr class="transition-colors hover:bg-primary/30 focus-within:bg-primary/30">
                        <td class="min-w-44 max-w-56">
                          <p class="truncate">{media.title}</p>
                        </td>
                        <td class="whitespace-nowrap text-sm">{normalizeDateDisplayValue(media.publishedAt)}</td>
                        <td>{media.type.name}</td>
                        <td>
                          <span
                            class={`badge badge-sm ${media.isDisplay ? 'badge-success badge-soft' : 'badge-ghost'}`}
                          >
                            {media.isDisplay ? '表示する' : '表示しない'}
                          </span>
                        </td>
                        <td class="max-w-xl">
                          <a
                            href={media.url}
                            target="_blank"
                            rel="noreferrer"
                            class="link link-hover break-all text-sm"
                          >
                            {media.url}
                          </a>
                        </td>
                        <td>
                          <a
                            href={`/media/${media.mediaId}?back=${encodeURIComponent(window.location.search)}`}
                            class="btn btn-ghost btn-xs"
                          >
                            編集
                          </a>
                        </td>
                      </tr>
                    )}
                  </For>
                )}
              </Match>
            </Switch>
          </tbody>
        </table>
      </div>

      <Show when={!data.loading && !fetchError() && (data()?.maxPage ?? 0) > 1}>
        <div class="mt-4 flex justify-center">
          <div class="join">
            <For each={Array.from({ length: data()!.maxPage }, (_, index) => index + 1)}>
              {p => (
                <button
                  class={`join-item btn btn-sm${p === page() ? ' btn-active' : ''}`}
                  onClick={() => handlePageChange(p)}
                >
                  {p}
                </button>
              )}
            </For>
          </div>
        </div>
      </Show>
    </>
  );
};
