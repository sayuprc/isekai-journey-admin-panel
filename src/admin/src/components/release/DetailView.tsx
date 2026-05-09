import { For, Show, createMemo, createSignal, onMount } from 'solid-js';
import type {
  ReleaseDistributionTypeValue,
  ReleaseGetResponse,
  ReleaseReferencedSong,
  ReleaseTypeValue,
  SongSummary,
} from '../../generated';
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

interface Props {
  data?: ReleaseGetResponse;
  status: number;
}

const normalizeDateValue = (value: unknown): string => {
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

const toTrackEntryForm = (song: ReleaseReferencedSong): TrackEntryForm => ({
  songId: song.songId,
  title: song.title,
});

export const DetailView = (props: Props) => {
  const listUrl = (() => {
    if (typeof window === 'undefined') {
      return '/releases';
    }

    const back = new URLSearchParams(window.location.search).get('back') ?? '';

    if (!back.startsWith('?')) {
      return '/releases';
    }

    try {
      const query = new URLSearchParams(back.slice(1)).toString();
      return query ? `/releases?${query}` : '/releases';
    } catch {
      return '/releases';
    }
  })();

  const [title, setTitle] = createSignal(props.data?.release.title ?? '');
  const [typeValue, setTypeValue] = createSignal<ReleaseTypeValue>(props.data?.release.typeValue ?? 1);
  const [distributionTypeValue, setDistributionTypeValue] = createSignal<ReleaseDistributionTypeValue>(
    props.data?.release.distributionTypeValue ?? 1,
  );
  const [releasedOn, setReleasedOn] = createSignal(normalizeDateValue(props.data?.release.releasedOn));
  const [description, setDescription] = createSignal(props.data?.release.description ?? '');
  const [isDisplay, setIsDisplay] = createSignal(props.data?.release.isDisplay ?? true);
  const [trackEntries, setTrackEntries] = createSignal<TrackEntryForm[]>((props.data?.songs ?? []).map(toTrackEntryForm));

  const [searchTitle, setSearchTitle] = createSignal('');
  const [searchResults, setSearchResults] = createSignal<SongSummary[]>([]);
  const [searchError, setSearchError] = createSignal<string | null>(null);
  const [isSearching, setIsSearching] = createSignal(false);
  const [hasSearched, setHasSearched] = createSignal(false);

  const { formError, setFormError, getFieldError, clearErrors, handleError } = createFormErrors();
  const { isSubmitting, withSubmitting } = createSubmitting();

  const selectedSongIds = createMemo(() => new Set(trackEntries().map(entry => entry.songId)));

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

  const removeTrackEntry = (songId: string) => {
    setTrackEntries(prev => prev.filter(entry => entry.songId !== songId));
  };

  const addTrackEntry = (song: SongSummary) => {
    if (selectedSongIds().has(song.songId)) {
      return;
    }

    setTrackEntries(prev => [...prev, { songId: song.songId, title: song.title }]);
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

    const releaseId = props.data?.release.releaseId;

    if (!releaseId) {
      setFormError('更新対象のリリースIDを取得できませんでした');
      return;
    }

    const { data, error, status } = await client.api.releases({ releaseId }).put({
      title: title(),
      typeValue: typeValue(),
      distributionTypeValue: distributionTypeValue(),
      releasedOn: releasedOn(),
      description: description(),
      isDisplay: isDisplay(),
      trackEntries: trackEntries().map((entry, index) => ({
        songId: entry.songId,
        trackNo: index + 1,
      })),
    });

    if (data) {
      setFlash('更新しました');
      window.location.href = listUrl;
      return;
    }

    if (status === 404) {
      setFlash('データがありません', 'error');
      window.location.href = listUrl;
      return;
    }

    handleError(status, error);
  });

  onMount(() => {
    if (props.status === 404) {
      setFlash('データがありません', 'error');
      window.location.href = listUrl;
      return;
    }

    if (props.status === 422) {
      setFlash('不正なリクエストです', 'error');
      window.location.href = listUrl;
      return;
    }

    if (!props.data) {
      setFormError('予期しないエラーが発生しました');
    }
  });

  return (
    <Show when={props.data} fallback={<p>読み込み中...</p>}>
      <a href={listUrl} class="btn btn-ghost btn-sm mb-4">
        ← 一覧に戻る
      </a>
      <FormError message={formError()} onClose={clearErrors} />
      <div class="max-w-5xl space-y-6">
        <form onSubmit={handleSubmit}>
          <fieldset class="fieldset rounded-box border border-base-300 bg-base-200 p-6">
            <legend class="px-2 text-sm font-semibold text-base-content/70">基本情報</legend>
            <div class="grid gap-5 md:grid-cols-2">
              <div>
                <label class="label">タイトル</label>
                <input
                  type="text"
                  class="input w-full"
                  value={title()}
                  onInput={e => setTitle(e.currentTarget.value)}
                  classList={{ 'input-error': !!getFieldError('title') }}
                />
                <Show when={getFieldError('title')}>{message => <p class="mt-1 text-xs text-error">{message()}</p>}</Show>
              </div>

              <div>
                <label class="label">発売日</label>
                <input
                  type="date"
                  class="input w-full"
                  value={releasedOn()}
                  onInput={e => setReleasedOn(e.currentTarget.value)}
                  classList={{ 'input-error': !!getFieldError('releasedOn') }}
                />
                <Show when={getFieldError('releasedOn')}>{message => <p class="mt-1 text-xs text-error">{message()}</p>}</Show>
              </div>

              <div>
                <label class="label">種別</label>
                <select
                  class="select select-bordered w-full"
                  value={String(typeValue())}
                  onChange={e => setTypeValue(Number(e.currentTarget.value) as ReleaseTypeValue)}
                >
                  <For each={RELEASE_TYPE_OPTIONS}>{option => <option value={option.value}>{option.label}</option>}</For>
                </select>
                <Show when={getFieldError('typeValue')}>{message => <p class="mt-1 text-xs text-error">{message()}</p>}</Show>
              </div>

              <div>
                <label class="label">流通形態</label>
                <select
                  class="select select-bordered w-full"
                  value={String(distributionTypeValue())}
                  onChange={e => setDistributionTypeValue(Number(e.currentTarget.value) as ReleaseDistributionTypeValue)}
                >
                  <For each={DISTRIBUTION_TYPE_OPTIONS}>{option => <option value={option.value}>{option.label}</option>}</For>
                </select>
                <Show when={getFieldError('distributionTypeValue')}>
                  {message => <p class="mt-1 text-xs text-error">{message()}</p>}
                </Show>
              </div>

              <div class="md:col-span-2">
                <label class="label">説明</label>
                <textarea
                  class="textarea textarea-bordered min-h-32 w-full"
                  value={description()}
                  onInput={e => setDescription(e.currentTarget.value)}
                  classList={{ 'textarea-error': !!getFieldError('description') }}
                />
                <Show when={getFieldError('description')}>
                  {message => <p class="mt-1 text-xs text-error">{message()}</p>}
                </Show>
              </div>

              <div class="md:col-span-2">
                <label class="label">表示設定</label>
                <div class="flex flex-wrap gap-4 rounded-box border border-base-300 bg-base-100 p-4">
                  <label class="label cursor-pointer justify-start gap-3">
                    <input
                      type="radio"
                      class="radio radio-sm"
                      checked={isDisplay()}
                      onChange={() => setIsDisplay(true)}
                    />
                    <span class="label-text">表示する</span>
                  </label>
                  <label class="label cursor-pointer justify-start gap-3">
                    <input
                      type="radio"
                      class="radio radio-sm"
                      checked={!isDisplay()}
                      onChange={() => setIsDisplay(false)}
                    />
                    <span class="label-text">表示しない</span>
                  </label>
                </div>
              </div>
            </div>

            <div class="mt-6 flex justify-end">
              <button class="btn btn-primary" disabled={isSubmitting()}>
                {isSubmitting() ? '更新中...' : '更新'}
              </button>
            </div>
          </fieldset>
        </form>

        <fieldset class="rounded-box border border-base-300 bg-base-100 p-6">
          <legend class="px-2 text-sm font-semibold text-base-content/70">収録楽曲</legend>
          <Show when={getFieldError('trackEntries')}>{message => <p class="mb-4 text-sm text-error">{message()}</p>}</Show>
          <Show
            when={trackEntries().length > 0}
            fallback={<p class="text-sm text-base-content/60">収録楽曲はまだ登録されていません。</p>}
          >
            <div class="overflow-x-auto">
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
                            <button type="button" class="btn btn-outline btn-error btn-xs" onClick={() => removeTrackEntry(entry.songId)}>
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
        </fieldset>

        <fieldset class="rounded-box border border-base-300 bg-base-100 p-6">
          <legend class="px-2 text-sm font-semibold text-base-content/70">楽曲を追加</legend>
          <form onSubmit={handleSongSearch} class="flex flex-col gap-4 md:flex-row md:items-end">
            <div class="flex-1">
              <label class="label">楽曲名</label>
              <input
                type="text"
                class="input input-bordered w-full"
                value={searchTitle()}
                onInput={e => setSearchTitle(e.currentTarget.value)}
                placeholder="楽曲名で検索"
              />
            </div>
            <button type="submit" class="btn btn-primary" disabled={isSearching()}>
              {isSearching() ? '検索中...' : '検索'}
            </button>
          </form>

          <Show when={searchError()}>
            {message => <p class="mt-3 text-sm text-error">{message()}</p>}
          </Show>

          <Show when={hasSearched()}>
            <div class="mt-4 overflow-x-auto">
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
                  <Show when={searchResults().length > 0} fallback={<tr><td colSpan={4} class="text-center text-sm text-base-content/60">条件に一致する楽曲はありません。</td></tr>}>
                    <For each={searchResults()}>
                      {song => (
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
        </fieldset>
      </div>
    </Show>
  );
};
