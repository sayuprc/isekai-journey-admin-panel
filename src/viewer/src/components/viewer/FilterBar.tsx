import { For, Show, createEffect, createSignal } from 'solid-js';

type FilterOption = { value: string; label: string };

type Props = {
  filters: FilterOption[];
  // 対象 entry を絞る CSS セレクタ
  entrySelector: string;
  // entry の dataset から、フィルター値と一致するか判定するキー（複数指定可、いずれかに一致すれば match）
  categoryAttrs: string[];
  // 検索対象テキストを保持する dataset キー（未指定なら検索 UI を出さない）
  searchAttr?: string;
  searchPlaceholder?: string;
  defaultFilter?: string;
};

export default function FilterBar(props: Props) {
  const [filter, setFilter] = createSignal(props.defaultFilter ?? 'all');
  const [query, setQuery] = createSignal('');

  createEffect(() => {
    const f = filter();
    const q = query().trim().toLowerCase();
    const entries = document.querySelectorAll(props.entrySelector);

    for (const entry of entries) {
      if (!(entry instanceof HTMLElement)) continue;
      const categoryMatch = f === 'all' || props.categoryAttrs.some(attr => entry.dataset[attr] === f);
      const searchKey = props.searchAttr;
      const text = searchKey ? entry.dataset[searchKey] ?? '' : '';
      const searchMatch = !searchKey || q === '' || text.includes(q);
      entry.style.display = categoryMatch && searchMatch ? '' : 'none';
    }
  });

  return (
    <div class="viewer-filters">
      <For each={props.filters}>
        {option => (
          <button
            class={`btn${filter() === option.value ? ' is-active' : ''}`}
            type="button"
            onClick={() => setFilter(option.value)}
          >
            {option.label}
          </button>
        )}
      </For>
      <Show when={props.searchAttr}>
        <div class="viewer-filters-spacer" />
        <input
          class="viewer-search"
          type="text"
          placeholder={props.searchPlaceholder}
          value={query()}
          onInput={event => setQuery(event.currentTarget.value)}
        />
      </Show>
    </div>
  );
}
