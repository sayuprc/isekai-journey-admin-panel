import { For, Match, Show, Switch, createResource, createSignal } from 'solid-js';
import type { MediaTypeValue } from '../../generated';
import { client } from '../../utils/client';
import { normalizeDateTimeDisplayValue } from '../../utils/date';
import { ListState } from '../ListState';
import { Pagination } from '../Pagination';

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

/** 一覧では URL 全体は幅に見合わないため、投稿先が分かるホスト名だけを出す(解析できない値は素のまま表示する) */
const toHostLabel = (url: string): string => {
  try {
    return new URL(url).hostname.replace(/^www\./, '');
  } catch {
    return url;
  }
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
        <table class="table table-sm table-zebra md:table-md">
          <thead>
            <tr>
              <th>タイトル</th>
              <th>公開日</th>
              <th>種別</th>
              <th>表示設定</th>
              <th>リンク</th>
            </tr>
          </thead>
          <tbody>
            <Switch>
              <Match when={data.loading}>
                <ListState state="loading" colSpan={5} />
              </Match>
              <Match when={fetchError()}>
                {message => <ListState state="error" colSpan={5} message={message()} onRetry={() => refetch()} />}
              </Match>
              <Match when={data() && data()!.media.length === 0}>
                <ListState state="empty" colSpan={5} message="条件に一致するメディアはありません。" />
              </Match>
              <Match when={data()}>
                {result => (
                  <For each={result().media}>
                    {media => (
                      <tr class="transition-colors hover:bg-primary/30 focus-within:bg-primary/30">
                        <td class="min-w-44 max-w-56">
                          <a
                            href={`/media/${media.mediaId}?back=${encodeURIComponent(window.location.search)}`}
                            class="link link-hover block truncate font-medium"
                          >
                            {media.title}
                          </a>
                        </td>
                        <td class="whitespace-nowrap text-sm">{normalizeDateTimeDisplayValue(media.publishedAt)}</td>
                        <td class="whitespace-nowrap">{media.type.name}</td>
                        <td class="whitespace-nowrap">
                          <span
                            class={`badge badge-sm ${media.isDisplay ? 'badge-success badge-soft' : 'badge-ghost'}`}
                          >
                            {media.isDisplay ? '表示する' : '表示しない'}
                          </span>
                        </td>
                        <td class="max-w-40">
                          <a
                            href={media.url}
                            target="_blank"
                            rel="noreferrer"
                            title={media.url}
                            class="link link-hover block truncate whitespace-nowrap text-sm"
                          >
                            {toHostLabel(media.url)}
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
        <Pagination page={page()} maxPage={data()!.maxPage} onChange={handlePageChange} />
      </Show>
    </>
  );
};
