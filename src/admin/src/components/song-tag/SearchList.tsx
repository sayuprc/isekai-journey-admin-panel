import { For, Match, Show, Switch, createResource, createSignal } from 'solid-js';
import { client } from '../../utils/client';

const DEFAULT_SORT = 'order_no';
const DEFAULT_ORDER = 'asc';
const DEFAULT_PAGE = 1;
const DEFAULT_PER_PAGE = 25;
const PER_PAGE_OPTIONS = [25, 50, 100] as const;

type PerPage = (typeof PER_PAGE_OPTIONS)[number];

type SearchParams = {
  name: string;
  sort: 'name' | 'order_no';
  order: 'asc' | 'desc';
  page: number;
  perPage: PerPage;
};

type SongTag = {
  songTagId: string;
  name: string;
  orderNo: number;
};

type SearchListData = {
  tags: SongTag[];
  maxPage: number;
};

const getInitialParams = (): SearchParams => {
  const params = new URLSearchParams(window.location.search);
  const perPageRaw = Number(params.get('per_page'));
  const sort = params.get('sort');
  const order = params.get('order');

  return {
    name: params.get('name') ?? '',
    sort: sort === 'name' || sort === 'order_no' ? sort : DEFAULT_SORT,
    order: order === 'desc' || order === 'asc' ? order : DEFAULT_ORDER,
    page: Number(params.get('page') ?? String(DEFAULT_PAGE)) || DEFAULT_PAGE,
    perPage: (PER_PAGE_OPTIONS.includes(perPageRaw as PerPage) ? perPageRaw : DEFAULT_PER_PAGE) as PerPage,
  };
};

const updateUrl = (params: SearchParams) => {
  const searchParams = new URLSearchParams();

  if (params.name) searchParams.set('name', params.name);
  if (params.sort !== DEFAULT_SORT) searchParams.set('sort', params.sort);
  if (params.order !== DEFAULT_ORDER) searchParams.set('order', params.order);
  if (params.page !== DEFAULT_PAGE) searchParams.set('page', String(params.page));
  if (params.perPage !== DEFAULT_PER_PAGE) searchParams.set('per_page', String(params.perPage));

  const query = searchParams.toString();
  history.pushState(null, '', query ? `?${query}` : window.location.pathname);
};

const shouldUseSearch = (params: SearchParams) =>
  params.name !== ''
  || params.sort !== DEFAULT_SORT
  || params.order !== DEFAULT_ORDER
  || params.page !== DEFAULT_PAGE
  || params.perPage !== DEFAULT_PER_PAGE;

export const SearchList = () => {
  const initial = getInitialParams();

  const [name, setName] = createSignal(initial.name);
  const [sort, setSort] = createSignal<SearchParams['sort']>(initial.sort);
  const [order, setOrder] = createSignal<SearchParams['order']>(initial.order);
  const [page, setPage] = createSignal(initial.page);
  const [perPage, setPerPage] = createSignal<PerPage>(initial.perPage);

  const [inputName, setInputName] = createSignal(initial.name);
  const [inputSort, setInputSort] = createSignal<SearchParams['sort']>(initial.sort);
  const [inputOrder, setInputOrder] = createSignal<SearchParams['order']>(initial.order);
  const [inputPerPage, setInputPerPage] = createSignal<PerPage>(initial.perPage);
  const [fetchError, setFetchError] = createSignal<string | null>(null);

  const [data] = createResource(
    () =>
      ({
        name: name(),
        sort: sort(),
        order: order(),
        page: page(),
        perPage: perPage(),
      }) satisfies SearchParams,
    async (params): Promise<SearchListData | undefined> => {
      setFetchError(null);

      if (!shouldUseSearch(params)) {
        const { data, status } = await client.api['song-tags'].get();

        if (status === 401) {
          window.location.href = '/auth/login';
          return;
        }

        if (!data) {
          setFetchError('データの取得に失敗しました。再度お試しください。');
          return;
        }

        return {
          tags: data.tags,
          maxPage: 1,
        };
      }

      const { data, status } = await client.api['song-tags'].search.get({
        query: {
          name: params.name,
          sort: params.sort,
          order: params.order,
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

      return {
        tags: data.tags,
        maxPage: data.maxPage,
      };
    },
  );

  const handleSearch = (e: Event) => {
    e.preventDefault();

    const params: SearchParams = {
      name: inputName(),
      sort: inputSort(),
      order: inputOrder(),
      page: DEFAULT_PAGE,
      perPage: inputPerPage(),
    };

    setName(params.name);
    setSort(params.sort);
    setOrder(params.order);
    setPage(params.page);
    setPerPage(params.perPage);
    updateUrl(params);
  };

  const handlePageChange = (nextPage: number) => {
    const params: SearchParams = {
      name: name(),
      sort: sort(),
      order: order(),
      page: nextPage,
      perPage: perPage(),
    };

    setPage(nextPage);
    updateUrl(params);
  };

  return (
    <>
      <form onSubmit={handleSearch} class="mb-4 flex flex-wrap items-end gap-4">
        <fieldset class="fieldset">
          <label class="fieldset-label" for="name">
            楽曲タグ名
          </label>
          <input
            type="text"
            id="name"
            name="name"
            value={inputName()}
            onInput={e => setInputName(e.currentTarget.value)}
            class="input input-bordered input-sm"
            placeholder="楽曲タグ名で検索"
          />
        </fieldset>
        <fieldset class="fieldset">
          <label class="fieldset-label" for="sort">
            ソート項目
          </label>
          <select
            id="sort"
            name="sort"
            class="select select-bordered select-sm"
            onChange={e => setInputSort(e.currentTarget.value as SearchParams['sort'])}
          >
            <option value="order_no" selected={inputSort() === 'order_no'}>
              表示順
            </option>
            <option value="name" selected={inputSort() === 'name'}>
              楽曲タグ名
            </option>
          </select>
        </fieldset>
        <fieldset class="fieldset">
          <label class="fieldset-label" for="order">
            並び順
          </label>
          <select
            id="order"
            name="order"
            class="select select-bordered select-sm"
            onChange={e => setInputOrder(e.currentTarget.value as SearchParams['order'])}
          >
            <option value="asc" selected={inputOrder() === 'asc'}>
              昇順
            </option>
            <option value="desc" selected={inputOrder() === 'desc'}>
              降順
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
            <For each={PER_PAGE_OPTIONS}>{n => <option value={n} selected={inputPerPage() === n}>{n}件</option>}</For>
          </select>
        </fieldset>
        <button type="submit" class="btn btn-primary btn-sm">
          検索
        </button>
      </form>
      <div class="rounded-box border border-base-300 bg-base-100 overflow-x-auto">
        <table class="table table-zebra">
          <thead>
            <tr>
              <th>楽曲タグ名</th>
              <th>表示順</th>
            </tr>
          </thead>
          <tbody>
            <Switch>
              <Match when={data.loading}>
                <For each={Array.from({ length: 5 })}>
                  {() => (
                    <tr>
                      <td>
                        <div class="skeleton h-4 w-32" />
                      </td>
                      <td>
                        <div class="skeleton h-4 w-8" />
                      </td>
                    </tr>
                  )}
                </For>
              </Match>
              <Match when={fetchError()}>
                {message => (
                  <tr>
                    <td colspan="2" class="py-8 text-center text-error">
                      {message()}
                    </td>
                  </tr>
                )}
              </Match>
              <Match when={data() && data()!.tags.length === 0}>
                <tr>
                  <td colspan="2" class="py-8 text-center text-base-content/70">
                    楽曲タグが見つかりませんでした。
                  </td>
                </tr>
              </Match>
              <Match when={data()}>
                {result => (
                  <For each={result().tags}>
                    {songTag => (
                      <tr class="hover:bg-primary/30 transition-colors">
                        <td>{songTag.name}</td>
                        <td>{songTag.orderNo}</td>
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
            <For each={Array.from({ length: data()!.maxPage }, (_, i) => i + 1)}>
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
