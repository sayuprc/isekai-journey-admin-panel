import { createSignal, For, Show } from 'solid-js';
import type { Accessor, Setter } from 'solid-js';
import type { Person } from '../../generated';
import { client } from '../../utils/client';
import { addSelectedPerson, moveSelectedPerson, type SelectedPerson } from './person-selection';

interface Props {
  label: string;
  selected: Accessor<SelectedPerson[]>;
  setSelected: Setter<SelectedPerson[]>;
}

const PER_PAGE = 25;

export const PersonSearchSection = (props: Props) => {
  const [query, setQuery] = createSignal('');
  const [searchedName, setSearchedName] = createSignal('');
  const [results, setResults] = createSignal<Person[]>([]);
  const [page, setPage] = createSignal(1);
  const [maxPage, setMaxPage] = createSignal(1);
  const [searching, setSearching] = createSignal(false);
  const [hasSearched, setHasSearched] = createSignal(false);
  const [searchError, setSearchError] = createSignal<string | null>(null);

  const selectedIds = () => new Set(props.selected().map(person => person.personId));

  const search = async (name: string, nextPage = 1) => {
    if (searching()) {
      return;
    }

    if (name === '') {
      setSearchError('人物名を入力してください');
      setResults([]);
      setHasSearched(false);
      return;
    }

    setSearching(true);
    setSearchError(null);
    setHasSearched(true);
    setSearchedName(name);

    const { data, status } = await client.api.persons.search.get({
      query: {
        name,
        sort: 'name',
        order: 'asc',
        page: nextPage,
        per_page: PER_PAGE,
      },
    });

    setSearching(false);

    if (status === 401) {
      window.location.href = '/auth/login';
      return;
    }

    if (!data) {
      setSearchError(`検索に失敗しました (${status})`);
      setResults([]);
      return;
    }

    setResults(data.persons);
    setPage(nextPage);
    setMaxPage(data.maxPage);
  };

  const addPerson = (person: Person) => {
    props.setSelected(current => addSelectedPerson(current, { personId: person.personId, name: person.name }));
  };

  const removePerson = (personId: string) => {
    props.setSelected(current => current.filter(person => person.personId !== personId));
  };

  return (
    <section class="space-y-3 rounded-box border border-base-300 bg-base-100 p-4">
      <h3 class="font-semibold">{props.label}</h3>

      <div>
        <label class="label" for={`${props.label}-person-search`}>人物名</label>
        <div class="flex gap-2">
          <input
            id={`${props.label}-person-search`}
            type="text"
            class="input input-bordered min-w-0 flex-1"
            value={query()}
            placeholder="人物名で検索"
            onInput={event => setQuery(event.currentTarget.value)}
            onKeyDown={(event) => {
              if (event.key === 'Enter') {
                event.preventDefault();
                void search(query().trim());
              }
            }}
          />
          <button
            type="button"
            class="btn btn-outline"
            disabled={searching()}
            onClick={() => void search(query().trim())}
          >
            {searching() ? '検索中...' : '検索'}
          </button>
        </div>
        <Show when={searchError()}>{message => <p class="mt-2 text-sm text-error">{message()}</p>}</Show>
      </div>

      <Show when={hasSearched() && !searchError()}>
        <div class="space-y-2">
          <Show
            when={results().length > 0}
            fallback={<p class="text-sm text-base-content/60">条件に一致する人物はありません。</p>}
          >
            <For each={results()}>
              {person => (
                <div class="flex items-center justify-between gap-3 rounded-box border border-base-300 p-3">
                  <div class="flex min-w-0 items-center gap-2">
                    <span class="truncate">{person.name}</span>
                    <Show when={selectedIds().has(person.personId)}>
                      <span class="badge badge-sm badge-primary badge-soft">追加済み</span>
                    </Show>
                  </div>
                  <button
                    type="button"
                    class="btn btn-primary btn-xs"
                    disabled={selectedIds().has(person.personId)}
                    onClick={() => addPerson(person)}
                  >
                    追加
                  </button>
                </div>
              )}
            </For>
          </Show>

          <Show when={maxPage() > 1}>
            <div class="flex items-center justify-between text-xs text-base-content/60">
              <span>{page()} / {maxPage()} ページ</span>
              <div class="flex gap-2">
                <button
                  type="button"
                  class="btn btn-ghost btn-xs"
                  disabled={searching() || page() <= 1}
                  onClick={() => void search(searchedName(), page() - 1)}
                >
                  前へ
                </button>
                <button
                  type="button"
                  class="btn btn-ghost btn-xs"
                  disabled={searching() || page() >= maxPage()}
                  onClick={() => void search(searchedName(), page() + 1)}
                >
                  次へ
                </button>
              </div>
            </div>
          </Show>
        </div>
      </Show>

      <div>
        <p class="label">選択中</p>
        <Show
          when={props.selected().length > 0}
          fallback={<p class="text-sm text-base-content/60">{props.label}はまだ追加されていません。</p>}
        >
          <div class="space-y-2">
            <For each={props.selected()}>
              {(person, index) => (
                <div class="flex items-center justify-between gap-3 rounded-box border border-base-300 p-3">
                  <span class="min-w-0 truncate">{person.name}</span>
                  <div class="flex shrink-0 gap-1">
                    <button
                      type="button"
                      class="btn btn-ghost btn-xs"
                      aria-label={`${person.name}を上へ移動`}
                      disabled={index() === 0}
                      onClick={() => props.setSelected(current => moveSelectedPerson(current, index(), -1))}
                    >
                      ↑
                    </button>
                    <button
                      type="button"
                      class="btn btn-ghost btn-xs"
                      aria-label={`${person.name}を下へ移動`}
                      disabled={index() === props.selected().length - 1}
                      onClick={() => props.setSelected(current => moveSelectedPerson(current, index(), 1))}
                    >
                      ↓
                    </button>
                    <button
                      type="button"
                      class="btn btn-ghost btn-xs text-error"
                      onClick={() => removePerson(person.personId)}
                    >
                      削除
                    </button>
                  </div>
                </div>
              )}
            </For>
          </div>
        </Show>
      </div>
    </section>
  );
};
