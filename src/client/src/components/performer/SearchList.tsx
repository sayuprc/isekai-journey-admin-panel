import { Show, createResource, createSignal, For, Match, Switch } from 'solid-js';
import { client } from '../../utils/client';

const PER_PAGE_OPTIONS = [25, 50, 100] as const;
type PerPage = (typeof PER_PAGE_OPTIONS)[number];

const getInitialParams = () => {
  const params = new URLSearchParams(window.location.search);
  const perPageRaw = Number(params.get('per_page'));
  return {
    name: params.get('name') ?? '',
    sort: params.get('sort') ?? 'order_no',
    order: params.get('order') ?? 'asc',
    page: Number(params.get('page') ?? '1') || 1,
    perPage: (PER_PAGE_OPTIONS.includes(perPageRaw as PerPage) ? perPageRaw : 25) as PerPage,
  };
};

export const SearchList = () => {
  const initial = getInitialParams();

  const [name, setName] = createSignal(initial.name);
  const [sort, setSort] = createSignal(initial.sort);
  const [order, setOrder] = createSignal(initial.order);
  const [page, setPage] = createSignal(initial.page);
  const [perPage, setPerPage] = createSignal<PerPage>(initial.perPage);

  // 検索フォームの一時入力値（Submit前）
  const [inputName, setInputName] = createSignal(initial.name);
  const [inputSort, setInputSort] = createSignal(initial.sort);
  const [inputOrder, setInputOrder] = createSignal(initial.order);
  const [inputPerPage, setInputPerPage] = createSignal<PerPage>(initial.perPage);

  const updateUrl = (params: { name: string; sort: string; order: string; page: number; perPage: number }) => {
    const searchParams = new URLSearchParams();
    if (params.name) searchParams.set('name', params.name);
    if (params.sort) searchParams.set('sort', params.sort);
    if (params.order) searchParams.set('order', params.order);
    searchParams.set('page', String(params.page));
    searchParams.set('per_page', String(params.perPage));
    history.pushState(null, '', `?${searchParams.toString()}`);
  };

  const [fetchError, setFetchError] = createSignal<string | null>(null);

  const [data] = createResource(
    () => ({ name: name(), sort: sort(), order: order(), page: page(), perPage: perPage() }),
    async (params) => {
      setFetchError(null);

      const { data, status } = await client.api.performers.search.get({
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

      return data;
    },
  );

  const handleSearch = (e: Event) => {
    e.preventDefault();

    // 検索時は必ず 1 ページ目に戻る
    const newPage = 1;

    setName(inputName());
    setSort(inputSort());
    setOrder(inputOrder());
    setPerPage(inputPerPage());
    setPage(newPage);
    updateUrl({ name: inputName(), sort: inputSort(), order: inputOrder(), page: newPage, perPage: inputPerPage() });
  };

  const handlePageChange = (page: number) => {
    setPage(page);
    updateUrl({ name: name(), sort: sort(), order: order(), page: page, perPage: perPage() });
  };

  return (
    <>
      <form onSubmit={handleSearch} class="mb-4 flex flex-wrap items-end gap-4">
        <fieldset class="fieldset">
          <label class="fieldset-label" for="name">
            共演者名
          </label>
          <input
            type="text"
            id="name"
            name="name"
            value={inputName()}
            onInput={(e) => setInputName(e.currentTarget.value)}
            class="input input-bordered input-sm"
            placeholder="共演者名で検索"
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
            onChange={(e) => setInputSort(e.currentTarget.value)}
          >
            <option value="order_no" selected={inputSort() === 'order_no'}>
              表示順
            </option>
            <option value="name" selected={inputSort() === 'name'}>
              共演者名
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
            onChange={(e) => setInputOrder(e.currentTarget.value)}
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
            onChange={(e) => setInputPerPage(Number(e.currentTarget.value) as PerPage)}
          >
            <For each={PER_PAGE_OPTIONS}>{(n) => <option value={n} selected={inputPerPage() === n}>{n}件</option>}</For>
          </select>
        </fieldset>
        <button type="submit" class="btn btn-primary btn-sm">
          検索
        </button>
      </form>
      <div class="mb-4 flex justify-end">
        <a href="/performers/create" class="btn btn-primary btn-sm">
          新規作成
        </a>
      </div>
      <div class="rounded-box border border-base-300 bg-base-100 overflow-x-auto">
        <table class="table table-zebra">
          <thead>
            <tr>
              <th>共演者名</th>
              <th>表示順</th>
              <th>操作</th>
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
                      <td>
                        <div class="skeleton h-6 w-10" />
                      </td>
                    </tr>
                  )}
                </For>
              </Match>
              <Match when={fetchError()}>
                {(message) => (
                  <tr>
                    <td colspan="3" class="py-8 text-center text-error">
                      {message()}
                    </td>
                  </tr>
                )}
              </Match>
              <Match when={data()}>
                {(result) => (
                  <For each={result().performers}>
                    {(performer) => (
                      <tr class="hover:bg-primary/30 focus-within:bg-primary/30 transition-colors">
                        <td>{performer.name}</td>
                        <td>{performer.orderNo}</td>
                        <td>
                          <a href={`/performers/${performer.performerId}?back=${encodeURIComponent(window.location.search)}`} class="btn btn-ghost btn-xs">
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
            <For each={Array.from({ length: data()!.maxPage }, (_, i) => i + 1)}>
              {(p) => (
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
