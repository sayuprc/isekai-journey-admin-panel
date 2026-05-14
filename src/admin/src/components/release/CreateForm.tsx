import { For, Show, createMemo, createSignal } from 'solid-js';
import type { ReleaseDistributionTypeValue, ReleaseTypeValue, SongSummary } from '../../generated';
import { client } from '../../utils/client';
import { createFormErrors } from '../../utils/form-error';
import { createSubmitting } from '../../utils/use-submitting';
import { setFlash } from '../Flash';
import { FormError } from '../FormError';

const RELEASE_TYPE_OPTIONS: Array<{ value: ReleaseTypeValue; label: string }> = [
  { value: 1, label: 'シングル' },
  { value: 2, label: 'アルバム' },
  { value: 3, label: 'EP' },
  { value: 99, label: 'その他' },
];

const DISTRIBUTION_TYPE_OPTIONS: Array<{ value: ReleaseDistributionTypeValue; label: string }> = [
  { value: 1, label: '配信' },
  { value: 2, label: '物理' },
  { value: 99, label: 'その他' },
];

type TrackEntryForm = {
  songId: string;
  title: string;
};

export const CreateForm = () => {
  const [typeValue, setTypeValue] = createSignal<ReleaseTypeValue>(1);
  const [distributionTypeValue, setDistributionTypeValue] = createSignal<ReleaseDistributionTypeValue>(1);
  const [isDisplay, setIsDisplay] = createSignal(true);
  const [trackEntries, setTrackEntries] = createSignal<TrackEntryForm[]>([]);
  const [searchTitle, setSearchTitle] = createSignal('');
  const [searchResults, setSearchResults] = createSignal<SongSummary[]>([]);
  const [searchError, setSearchError] = createSignal<string | null>(null);
  const [isSearching, setIsSearching] = createSignal(false);
  const [hasSearched, setHasSearched] = createSignal(false);

  const { formError, getFieldError, clearErrors, handleError } = createFormErrors();
  const { isSubmitting, withSubmitting } = createSubmitting();

  const selectedSongIds = createMemo(() => new Set(trackEntries().map((entry) => entry.songId)));

  const addTrackEntry = (song: SongSummary) => {
    if (selectedSongIds().has(song.songId)) {
      return;
    }

    setTrackEntries((prev) => [...prev, { songId: song.songId, title: song.title }]);
  };

  const removeTrackEntry = (songId: string) => {
    setTrackEntries((prev) => prev.filter((entry) => entry.songId !== songId));
  };

  const moveTrackEntry = (index: number, direction: -1 | 1) => {
    setTrackEntries((prev) => {
      const nextIndex = index + direction;
      if (nextIndex < 0 || nextIndex >= prev.length) {
        return prev;
      }

      const cloned = [...prev];
      const [entry] = cloned.splice(index, 1);
      if (!entry) {
        return prev;
      }
      cloned.splice(nextIndex, 0, entry);

      return cloned;
    });
  };

  const handleSongSearch = async (e: Event) => {
    e.preventDefault();
    setSearchError(null);
    setHasSearched(true);

    if (searchTitle().trim() === '') {
      setSearchResults([]);
      setSearchError('楽曲名を入力してください');
      return;
    }

    setIsSearching(true);

    const { data, error, status } = await client.api.songs.search.get({
      query: {
        title: searchTitle().trim(),
        sort: 'title',
        order: 'asc',
        page: 1,
        per_page: 25,
      },
    });

    setIsSearching(false);

    if (status === 401) {
      window.location.href = '/auth/login';
      return;
    }

    if (!data) {
      if (typeof error === 'object' && error !== null && 'value' in error) {
        const body = (error as { value?: { message?: string } }).value;
        setSearchError(body?.message ?? `検索に失敗しました (${status})`);
      } else {
        setSearchError(`検索に失敗しました (${status})`);
      }
      setSearchResults([]);
      return;
    }

    setSearchResults(data.songs);
  };

  const handleSubmit = withSubmitting(async (e: Event) => {
    e.preventDefault();
    clearErrors();

    const form = e.target as HTMLFormElement;
    const formData = new FormData(form);

    const { data, error, status } = await client.api.releases.post({
      title: formData.get('title')?.toString() ?? '',
      typeValue: typeValue(),
      distributionTypeValue: distributionTypeValue(),
      releasedOn: formData.get('releasedOn')?.toString() ?? '',
      description: formData.get('description')?.toString() ?? '',
      isDisplay: isDisplay(),
      trackEntries: trackEntries().map((entry, index) => ({
        songId: entry.songId,
        trackNo: index + 1,
      })),
    });

    if (data) {
      setFlash('作成しました');
      window.location.href = '/releases';
      return;
    }

    handleError(status, error);
  });

  return (
    <form onSubmit={handleSubmit}>
      <a href="/releases" class="btn btn-ghost btn-sm mb-4">
        ← 一覧に戻る
      </a>
      <FormError message={formError()} onClose={clearErrors} />
      <div class="max-w-5xl space-y-6">
        <fieldset class="fieldset bg-base-200 border-base-300 rounded-box border p-6">
          <legend class="px-2 text-sm font-semibold text-base-content/70">基本情報</legend>
          <div class="grid gap-5 md:grid-cols-2">
            <div>
              <label class="label">タイトル</label>
              <input
                type="text"
                class="input w-full"
                name="title"
                required
                classList={{ 'input-error': !!getFieldError('title') }}
              />
              <Show when={getFieldError('title')}>
                {(message) => <p class="mt-1 text-xs text-error">{message()}</p>}
              </Show>
            </div>

            <div>
              <label class="label">発売日</label>
              <input
                type="date"
                class="input w-full"
                name="releasedOn"
                required
                classList={{ 'input-error': !!getFieldError('releasedOn') }}
              />
              <Show when={getFieldError('releasedOn')}>
                {(message) => <p class="mt-1 text-xs text-error">{message()}</p>}
              </Show>
            </div>

            <div>
              <label class="label">種別</label>
              <select
                class="select select-bordered w-full"
                name="typeValue"
                value={String(typeValue())}
                onChange={(e) => setTypeValue(Number(e.currentTarget.value) as ReleaseTypeValue)}
              >
                <For each={RELEASE_TYPE_OPTIONS}>
                  {(option) => <option value={option.value}>{option.label}</option>}
                </For>
              </select>
              <Show when={getFieldError('typeValue')}>
                {(message) => <p class="mt-1 text-xs text-error">{message()}</p>}
              </Show>
            </div>

            <div>
              <label class="label">流通形態</label>
              <select
                class="select select-bordered w-full"
                name="distributionTypeValue"
                value={String(distributionTypeValue())}
                onChange={(e) =>
                  setDistributionTypeValue(Number(e.currentTarget.value) as ReleaseDistributionTypeValue)
                }
              >
                <For each={DISTRIBUTION_TYPE_OPTIONS}>
                  {(option) => <option value={option.value}>{option.label}</option>}
                </For>
              </select>
              <Show when={getFieldError('distributionTypeValue')}>
                {(message) => <p class="mt-1 text-xs text-error">{message()}</p>}
              </Show>
            </div>

            <div class="md:col-span-2">
              <label class="label">説明</label>
              <textarea
                class="textarea textarea-bordered min-h-32 w-full"
                name="description"
                classList={{ 'textarea-error': !!getFieldError('description') }}
              />
              <Show when={getFieldError('description')}>
                {(message) => <p class="mt-1 text-xs text-error">{message()}</p>}
              </Show>
            </div>

            <div class="md:col-span-2">
              <label class="label">表示設定</label>
              <select
                class="select select-bordered w-full"
                value={String(isDisplay())}
                onChange={(e) => setIsDisplay(e.currentTarget.value === 'true')}
                classList={{ 'select-error': !!getFieldError('isDisplay') }}
              >
                <option value="true">表示する</option>
                <option value="false">表示しない</option>
              </select>
              <Show when={getFieldError('isDisplay')}>
                {(message) => <p class="mt-1 text-xs text-error">{message()}</p>}
              </Show>
            </div>
          </div>
        </fieldset>

        <fieldset class="rounded-box border border-base-300 bg-base-200 p-6">
          <legend class="px-2 text-sm font-semibold text-base-content/70">収録楽曲</legend>
          <Show when={getFieldError('trackEntries')}>
            {(message) => <p class="mb-4 text-sm text-error">{message()}</p>}
          </Show>
          <div class="space-y-6">
            <div>
              <label class="label">現在の収録楽曲</label>
              <Show
                when={trackEntries().length > 0}
                fallback={<p class="text-sm text-base-content/60">収録楽曲はまだ登録されていません。</p>}
              >
                <div class="overflow-x-auto rounded-box border border-base-300 bg-base-100">
                  <table class="table table-sm">
                    <thead>
                      <tr>
                        <th>曲順</th>
                        <th>楽曲名</th>
                        <th class="text-right">操作</th>
                      </tr>
                    </thead>
                    <tbody>
                      <For each={trackEntries()}>
                        {(entry, index) => (
                          <tr>
                            <td>{index() + 1}</td>
                            <td>{entry.title}</td>
                            <td>
                              <div class="flex justify-end gap-2">
                                <button
                                  type="button"
                                  class="btn btn-ghost btn-xs"
                                  disabled={index() === 0}
                                  onClick={() => moveTrackEntry(index(), -1)}
                                >
                                  ↑
                                </button>
                                <button
                                  type="button"
                                  class="btn btn-ghost btn-xs"
                                  disabled={index() === trackEntries().length - 1}
                                  onClick={() => moveTrackEntry(index(), 1)}
                                >
                                  ↓
                                </button>
                                <a href={`/songs/${entry.songId}`} class="btn btn-ghost btn-xs">
                                  楽曲を見る
                                </a>
                                <button
                                  type="button"
                                  class="btn btn-outline btn-error btn-xs"
                                  onClick={() => removeTrackEntry(entry.songId)}
                                >
                                  削除
                                </button>
                              </div>
                            </td>
                          </tr>
                        )}
                      </For>
                    </tbody>
                  </table>
                </div>
              </Show>
            </div>

            <div>
              <label class="label">楽曲を追加</label>
              <div class="flex flex-col gap-4 md:flex-row md:items-end">
                <div class="flex-1">
                  <label class="label">楽曲名</label>
                  <input
                    type="text"
                    class="input input-bordered w-full"
                    value={searchTitle()}
                    onInput={(e) => setSearchTitle(e.currentTarget.value)}
                    placeholder="楽曲名で検索"
                  />
                </div>
                <button type="button" class="btn btn-primary" disabled={isSearching()} onClick={handleSongSearch}>
                  {isSearching() ? '検索中...' : '検索'}
                </button>
              </div>

              <Show when={searchError()}>{(message) => <p class="mt-3 text-sm text-error">{message()}</p>}</Show>

              <Show when={hasSearched()}>
                <div class="mt-4 overflow-x-auto rounded-box border border-base-300 bg-base-100">
                  <table class="table table-sm">
                    <thead>
                      <tr>
                        <th>楽曲名</th>
                        <th>種別</th>
                        <th>表示設定</th>
                        <th class="text-right">操作</th>
                      </tr>
                    </thead>
                    <tbody>
                      <Show
                        when={searchResults().length > 0}
                        fallback={
                          <tr>
                            <td colSpan={4} class="text-center text-sm text-base-content/60">
                              条件に一致する楽曲はありません。
                            </td>
                          </tr>
                        }
                      >
                        <For each={searchResults()}>
                          {(song) => (
                            <tr>
                              <td>{song.title}</td>
                              <td>{song.type.name}</td>
                              <td>{song.isDisplay ? '表示する' : '表示しない'}</td>
                              <td class="text-right">
                                <button
                                  type="button"
                                  class="btn btn-primary btn-xs"
                                  disabled={selectedSongIds().has(song.songId)}
                                  onClick={() => addTrackEntry(song)}
                                >
                                  {selectedSongIds().has(song.songId) ? '追加済み' : '追加'}
                                </button>
                              </td>
                            </tr>
                          )}
                        </For>
                      </Show>
                    </tbody>
                  </table>
                </div>
              </Show>
            </div>
          </div>
        </fieldset>

        <div class="flex justify-end">
          <button class="btn btn-primary" disabled={isSubmitting()}>
            {isSubmitting() ? '作成中...' : '作成'}
          </button>
        </div>
      </div>
    </form>
  );
};
