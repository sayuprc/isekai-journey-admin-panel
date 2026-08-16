import { For, Match, Show, Switch, createResource, createSignal } from 'solid-js';
import type { PlaceKindValue, SortOrder } from '../../generated';
import { client } from '../../utils/client';
import { ListState } from '../ListState';
import { Pagination } from '../Pagination';

const PER_PAGE_OPTIONS = [25, 50, 100] as const;
type PerPage = (typeof PER_PAGE_OPTIONS)[number];
type KindFilter = '' | `${PlaceKindValue}`;

const DEFAULT_PARAMS = {
  name: '',
  kindValue: '' as KindFilter,
  order: 'asc' as SortOrder,
  page: 1,
  perPage: 25 as PerPage,
};

const getInitialParams = () => {
  const params = new URLSearchParams(window.location.search);
  const perPageRaw = Number(params.get('per_page'));
  const kindRaw = params.get('kind_value');
  const kindValue = kindRaw === '1' || kindRaw === '2' ? kindRaw : DEFAULT_PARAMS.kindValue;
  const order = params.get('order');

  return {
    name: params.get('name') ?? DEFAULT_PARAMS.name,
    kindValue,
    order: order === 'asc' || order === 'desc' ? order : DEFAULT_PARAMS.order,
    page: Number(params.get('page') ?? String(DEFAULT_PARAMS.page)) || DEFAULT_PARAMS.page,
    perPage: (PER_PAGE_OPTIONS.includes(perPageRaw as PerPage) ? perPageRaw : DEFAULT_PARAMS.perPage) as PerPage,
  };
};

export const SearchList = () => {
  const initial = getInitialParams();

  const [name, setName] = createSignal(initial.name);
  const [kindValue, setKindValue] = createSignal<KindFilter>(initial.kindValue);
  const [order, setOrder] = createSignal(initial.order);
  const [page, setPage] = createSignal(initial.page);
  const [perPage, setPerPage] = createSignal<PerPage>(initial.perPage);

  const [inputName, setInputName] = createSignal(initial.name);
  const [inputKindValue, setInputKindValue] = createSignal<KindFilter>(initial.kindValue);
  const [inputOrder, setInputOrder] = createSignal(initial.order);
  const [inputPerPage, setInputPerPage] = createSignal<PerPage>(initial.perPage);

  const updateUrl = (params: {
    name: string;
    kindValue: KindFilter;
    order: SortOrder;
    page: number;
    perPage: number;
  }) => {
    const searchParams = new URLSearchParams();
    if (params.name) searchParams.set('name', params.name);
    if (params.kindValue) searchParams.set('kind_value', params.kindValue);
    if (params.order) searchParams.set('order', params.order);
    searchParams.set('page', String(params.page));
    searchParams.set('per_page', String(params.perPage));
    history.pushState(null, '', `?${searchParams.toString()}`);
  };

  const [fetchError, setFetchError] = createSignal<string | null>(null);

  const [data, { refetch }] = createResource(
    () => ({ name: name(), kindValue: kindValue(), order: order(), page: page(), perPage: perPage() }),
    async (params) => {
      setFetchError(null);

      const { data, status } = await client.api.places.search.get({
        query: {
          name: params.name,
          kindValue: params.kindValue ? (Number(params.kindValue) as PlaceKindValue) : undefined,
          sort: 'name',
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

      return data;
    },
  );

  const handleSearch = (e: Event) => {
    e.preventDefault();

    const newPage = 1;

    setName(inputName());
    setKindValue(inputKindValue());
    setOrder(inputOrder());
    setPerPage(inputPerPage());
    setPage(newPage);
    updateUrl({
      name: inputName(),
      kindValue: inputKindValue(),
      order: inputOrder(),
      page: newPage,
      perPage: inputPerPage(),
    });
  };

  const handlePageChange = (nextPage: number) => {
    setPage(nextPage);
    updateUrl({ name: name(), kindValue: kindValue(), order: order(), page: nextPage, perPage: perPage() });
  };

  const handleReset = () => {
    setInputName(DEFAULT_PARAMS.name);
    setInputKindValue(DEFAULT_PARAMS.kindValue);
    setInputOrder(DEFAULT_PARAMS.order);
    setInputPerPage(DEFAULT_PARAMS.perPage);

    setName(DEFAULT_PARAMS.name);
    setKindValue(DEFAULT_PARAMS.kindValue);
    setOrder(DEFAULT_PARAMS.order);
    setPage(DEFAULT_PARAMS.page);
    setPerPage(DEFAULT_PARAMS.perPage);

    updateUrl(DEFAULT_PARAMS);
  };

  return (
    <>
      <form onSubmit={handleSearch} class="mb-4 flex flex-wrap items-end gap-4">
        <fieldset class="fieldset">
          <label class="fieldset-label" for="name">
            場所名
          </label>
          <input
            type="text"
            id="name"
            name="name"
            value={inputName()}
            onInput={e => setInputName(e.currentTarget.value)}
            class="input input-bordered input-sm"
            placeholder="場所名で検索"
          />
        </fieldset>
        <fieldset class="fieldset">
          <label class="fieldset-label" for="kindValue">
            種別
          </label>
          <select
            id="kindValue"
            name="kindValue"
            class="select select-bordered select-sm"
            onChange={e => setInputKindValue(e.currentTarget.value as KindFilter)}
          >
            <option value="" selected={inputKindValue() === ''}>
              すべて
            </option>
            <option value="1" selected={inputKindValue() === '1'}>
              会場
            </option>
            <option value="2" selected={inputKindValue() === '2'}>
              配信先
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
            onChange={e => setInputOrder(e.currentTarget.value as SortOrder)}
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
        <a href="/places/create" class="btn btn-primary btn-sm">
          新規作成
        </a>
      </div>
      <div class="overflow-x-auto rounded-box border border-base-300 bg-base-100">
        <table class="table table-sm table-zebra md:table-md">
          <thead>
            <tr>
              <th>場所名</th>
              <th>種別</th>
              <th>操作</th>
            </tr>
          </thead>
          <tbody>
            <Switch>
              <Match when={data.loading}>
                <ListState state="loading" colSpan={3} />
              </Match>
              <Match when={fetchError()}>
                {message => <ListState state="error" colSpan={3} message={message()} onRetry={() => refetch()} />}
              </Match>
              <Match when={data() && data()!.places.length === 0}>
                <ListState state="empty" colSpan={3} />
              </Match>
              <Match when={data()}>
                {result => (
                  <For each={result().places}>
                    {place => (
                      <tr class="transition-colors hover:bg-primary/30 focus-within:bg-primary/30">
                        <td>
                          <a
                            href={`/places/${place.placeId}?back=${encodeURIComponent(window.location.search)}`}
                            class="link link-hover font-medium"
                          >
                            {place.name}
                          </a>
                        </td>
                        <td>{place.kind.name}</td>
                        <td>
                          <a
                            href={`/places/${place.placeId}?back=${encodeURIComponent(window.location.search)}`}
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
        <Pagination page={page()} maxPage={data()!.maxPage} onChange={handlePageChange} />
      </Show>
    </>
  );
};
