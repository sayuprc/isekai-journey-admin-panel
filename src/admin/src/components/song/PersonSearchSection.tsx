import { createSignal, For, Show } from 'solid-js';
import type { Accessor, Setter } from 'solid-js';
import type { Person, SongPersonRole } from '../../generated';
import { client } from '../../utils/client';
import {
  addSelectedPersonToRole,
  moveSelectedPerson,
  type PersonSelections,
} from './person-selection';

interface Props {
  selections: Accessor<PersonSelections>;
  setSelections: Setter<PersonSelections>;
}

const PER_PAGE = 25;
const ROLE_OPTIONS: { role: SongPersonRole; label: string }[] = [
  { role: 1, label: '作詞' },
  { role: 2, label: '作曲' },
  { role: 3, label: '編曲' },
];

export const PersonSearchSection = (props: Props) => {
  const [query, setQuery] = createSignal('');
  const [searchedName, setSearchedName] = createSignal('');
  const [results, setResults] = createSignal<Person[]>([]);
  const [page, setPage] = createSignal(1);
  const [maxPage, setMaxPage] = createSignal(1);
  const [searching, setSearching] = createSignal(false);
  const [hasSearched, setHasSearched] = createSignal(false);
  const [searchError, setSearchError] = createSignal<string | null>(null);
  const [targetRole, setTargetRole] = createSignal<SongPersonRole>(1);

  const selectedIds = () => new Set(props.selections()[targetRole()].map(person => person.personId));
  const targetLabel = () => ROLE_OPTIONS.find(option => option.role === targetRole())?.label ?? '';

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
    props.setSelections(current =>
      addSelectedPersonToRole(current, targetRole(), { personId: person.personId, name: person.name }));
  };

  const removePerson = (role: SongPersonRole, personId: string) => {
    props.setSelections(current => ({
      ...current,
      [role]: current[role].filter(person => person.personId !== personId),
    }));
  };

  const movePerson = (role: SongPersonRole, index: number, direction: -1 | 1) => {
    props.setSelections(current => ({
      ...current,
      [role]: moveSelectedPerson(current[role], index, direction),
    }));
  };

  return (
    <section class="space-y-4 rounded-box border border-base-300 bg-base-100 p-4">
      <div class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_auto]">
        <div>
          <label class="label" for="song-person-search">人物名</label>
          <div class="flex gap-2">
            <input
              id="song-person-search"
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
        <div>
          <label class="label" for="song-person-role">追加先</label>
          <select
            id="song-person-role"
            class="select select-bordered w-full sm:w-32"
            value={targetRole()}
            onChange={event => setTargetRole(Number(event.currentTarget.value) as SongPersonRole)}
          >
            <For each={ROLE_OPTIONS}>{option => <option value={option.role}>{option.label}</option>}</For>
          </select>
        </div>
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
                      <span class="badge badge-sm badge-primary badge-soft">{targetLabel()}に追加済み</span>
                    </Show>
                  </div>
                  <button
                    type="button"
                    class="btn btn-primary btn-xs"
                    disabled={selectedIds().has(person.personId)}
                    onClick={() => addPerson(person)}
                  >
                    {targetLabel()}に追加
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

      <div class="grid gap-3 lg:grid-cols-3">
        <For each={ROLE_OPTIONS}>
          {option => (
            <div class="rounded-box border border-base-300 p-3">
              <p class="font-semibold">{option.label}</p>
              <Show
                when={props.selections()[option.role].length > 0}
                fallback={<p class="mt-2 text-sm text-base-content/60">まだ追加されていません。</p>}
              >
                <div class="mt-2 space-y-2">
                  <For each={props.selections()[option.role]}>
                    {(person, index) => (
                      <div class="flex items-center justify-between gap-3 rounded-box border border-base-300 p-3">
                        <span class="min-w-0 truncate">{person.name}</span>
                        <div class="flex shrink-0 gap-1">
                          <button
                            type="button"
                            class="btn btn-ghost btn-xs"
                            aria-label={`${person.name}を上へ移動`}
                            disabled={index() === 0}
                            onClick={() => movePerson(option.role, index(), -1)}
                          >
                            ↑
                          </button>
                          <button
                            type="button"
                            class="btn btn-ghost btn-xs"
                            aria-label={`${person.name}を下へ移動`}
                            disabled={index() === props.selections()[option.role].length - 1}
                            onClick={() => movePerson(option.role, index(), 1)}
                          >
                            ↓
                          </button>
                          <button
                            type="button"
                            class="btn btn-ghost btn-xs text-error"
                            onClick={() => removePerson(option.role, person.personId)}
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
          )}
        </For>
      </div>
    </section>
  );
};
