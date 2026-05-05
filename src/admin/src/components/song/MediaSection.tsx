import { createSignal, For, Show, type Accessor, type Setter } from 'solid-js';
import type { Media, MediaFormatValue, MediaTypeValue, RequestSongMediaLink, SongLinkedMedia } from '../../generated';
import { client } from '../../utils/client';

export type MediaEntry = {
  mediaId: string;
  title: string;
  url: string;
  typeName: string;
  formatName: string;
  isDisplay: boolean;
};

interface Props {
  entries: Accessor<MediaEntry[]>;
  setEntries: Setter<MediaEntry[]>;
  availableMedia: Accessor<Media[]>;
  setAvailableMedia: Setter<Media[]>;
}

const mediaFormatOptions: Array<{ value: MediaFormatValue; label: string }> = [
  { value: 1, label: 'MV' },
  { value: 2, label: '音源動画' },
  { value: 3, label: '配信アーカイブ' },
  { value: 4, label: 'ショート動画' },
  { value: 5, label: 'ティザー' },
  { value: 6, label: 'ライブクリップ' },
  { value: 99, label: 'その他' },
];

const mediaTypeOptions: Array<{ value: MediaTypeValue; label: string }> = [
  { value: 1, label: '動画' },
  { value: 2, label: '記事' },
  { value: 3, label: 'SNS投稿' },
  { value: 4, label: '公式ページ' },
  { value: 99, label: 'その他' },
];

const mergeMedia = (current: Media[], incoming: Media[]): Media[] => {
  const map = new Map(current.map(item => [item.mediaId, item]));
  incoming.forEach(item => map.set(item.mediaId, item));

  return Array.from(map.values()).sort((a, b) => a.title.localeCompare(b.title, 'ja'));
};

const toErrorMessage = (error: unknown, fallback: string) => {
  if (typeof error === 'object' && error !== null && 'value' in error) {
    const value = (error as { value?: { message?: string; summary?: string } }).value;
    return value?.message ?? value?.summary ?? fallback;
  }

  return fallback;
};

export const toMediaEntry = (item: SongLinkedMedia | Media): MediaEntry => ({
  mediaId: item.mediaId,
  title: item.title,
  url: item.url,
  typeName: item.type.name,
  formatName: item.format.name,
  isDisplay: item.isDisplay,
});

export const buildSongMediaRequest = (entries: MediaEntry[]): RequestSongMediaLink[] =>
  entries.map((entry, index) => ({
    mediaId: entry.mediaId,
    orderNo: index + 1,
  }));

export const MediaSection = (props: Props) => {
  const [searchTitle, setSearchTitle] = createSignal('');
  const [searching, setSearching] = createSignal(false);
  const [searchResults, setSearchResults] = createSignal<Media[]>([]);
  const [searchError, setSearchError] = createSignal<string | null>(null);

  const [createTitle, setCreateTitle] = createSignal('');
  const [createUrl, setCreateUrl] = createSignal('');
  const [createTypeValue, setCreateTypeValue] = createSignal<MediaTypeValue>(1);
  const [createFormatValue, setCreateFormatValue] = createSignal<MediaFormatValue>(1);
  const [createIsDisplay, setCreateIsDisplay] = createSignal(true);
  const [creating, setCreating] = createSignal(false);
  const [createError, setCreateError] = createSignal<string | null>(null);

  const selectedIds = () => new Set(props.entries().map(entry => entry.mediaId));

  const addEntry = (media: Media) => {
    if (selectedIds().has(media.mediaId)) {
      return;
    }

    props.setEntries(prev => [...prev, toMediaEntry(media)]);
    props.setAvailableMedia(prev => mergeMedia(prev, [media]));
  };

  const removeEntry = (index: number) => {
    props.setEntries(prev => prev.filter((_, i) => i !== index));
  };

  const moveEntry = (index: number, direction: -1 | 1) => {
    props.setEntries((prev) => {
      const nextIndex = index + direction;
      if (nextIndex < 0 || nextIndex >= prev.length) {
        return prev;
      }

      const cloned = [...prev];
      const [item] = cloned.splice(index, 1);
      if (!item) {
        return prev;
      }
      cloned.splice(nextIndex, 0, item);

      return cloned;
    });
  };

  const handleSearch = async () => {
    setSearchError(null);
    setSearching(true);

    const { data, error, status } = await client.api.media.search.get({
      query: {
        title: searchTitle(),
        per_page: 25,
      },
    });

    setSearching(false);

    if (!data) {
      setSearchError(toErrorMessage(error, `検索に失敗しました (${status})`));
      return;
    }

    setSearchResults(data.media);
    props.setAvailableMedia(prev => mergeMedia(prev, data.media));
  };

  const handleCreate = async () => {
    const title = createTitle().trim();
    const url = createUrl().trim();

    setCreateError(null);

    if (title === '') {
      setCreateError('タイトルを入力してください');
      return;
    }

    if (url === '') {
      setCreateError('URLを入力してください');
      return;
    }

    setCreating(true);

    const { data, error, status } = await client.api.media.post({
      title,
      url,
      typeValue: createTypeValue(),
      formatValue: createFormatValue(),
      isDisplay: createIsDisplay(),
    });

    setCreating(false);

    if (!data) {
      setCreateError(toErrorMessage(error, `作成に失敗しました (${status})`));
      return;
    }

    props.setAvailableMedia(prev => mergeMedia(prev, [data.media]));
    addEntry(data.media);
    setCreateTitle('');
    setCreateUrl('');
    setCreateTypeValue(1);
    setCreateFormatValue(1);
    setCreateIsDisplay(true);
  };

  const unselectedResults = () => searchResults().filter(item => !selectedIds().has(item.mediaId));

  return (
    <fieldset class="fieldset bg-base-200 border-base-300 rounded-box border p-6">
      <legend class="px-2 text-sm font-semibold text-base-content/70">Media</legend>

      <div class="grid gap-6 lg:grid-cols-[minmax(0,1.3fr)_minmax(0,1fr)]">
        <div class="space-y-4">
          <div>
            <label class="label">既存 Media を検索</label>
            <div class="flex gap-2">
              <input
                type="text"
                class="input input-bordered w-full"
                value={searchTitle()}
                onInput={e => setSearchTitle(e.currentTarget.value)}
                placeholder="タイトルで検索"
              />
              <button type="button" class="btn btn-outline" disabled={searching()} onClick={handleSearch}>
                {searching() ? '検索中...' : '検索'}
              </button>
            </div>
            <Show when={searchError()}>{message => <p class="mt-2 text-sm text-error">{message()}</p>}</Show>
          </div>

          <div class="space-y-2">
            <Show
              when={unselectedResults().length > 0}
              fallback={<p class="text-sm text-base-content/60">検索結果はまだありません。</p>}
            >
              <For each={unselectedResults()}>
                {item => (
                  <div class="rounded-box border border-base-300 bg-base-100 p-3">
                    <div class="flex items-start justify-between gap-3">
                      <div class="min-w-0">
                        <p class="truncate font-medium">{item.title}</p>
                        <p class="text-xs text-base-content/60">{item.type.name} / {item.format.name}</p>
                        <a href={item.url} target="_blank" rel="noreferrer" class="link link-hover break-all text-xs">
                          {item.url}
                        </a>
                      </div>
                      <button type="button" class="btn btn-xs btn-primary" onClick={() => addEntry(item)}>
                        追加
                      </button>
                    </div>
                  </div>
                )}
              </For>
            </Show>
          </div>
        </div>

        <div class="space-y-3 rounded-box border border-base-300 bg-base-100 p-4">
          <div>
            <label class="label">新規 Media 作成</label>
            <input
              type="text"
              class="input input-bordered w-full"
              value={createTitle()}
              onInput={e => setCreateTitle(e.currentTarget.value)}
              placeholder="タイトル"
            />
          </div>

          <div>
            <input
              type="url"
              class="input input-bordered w-full"
              value={createUrl()}
              onInput={e => setCreateUrl(e.currentTarget.value)}
              placeholder="https://example.com/media"
            />
          </div>

          <div class="grid gap-3 md:grid-cols-2">
            <select
              class="select select-bordered w-full"
              value={createTypeValue()}
              onChange={e => setCreateTypeValue(Number(e.currentTarget.value) as MediaTypeValue)}
            >
              <For each={mediaTypeOptions}>{option => <option value={option.value}>{option.label}</option>}</For>
            </select>

            <select
              class="select select-bordered w-full"
              value={createFormatValue()}
              onChange={e => setCreateFormatValue(Number(e.currentTarget.value) as MediaFormatValue)}
            >
              <For each={mediaFormatOptions}>{option => <option value={option.value}>{option.label}</option>}</For>
            </select>
          </div>

          <label class="label cursor-pointer justify-start gap-3 rounded-box border border-base-300 px-3">
            <input
              type="checkbox"
              class="checkbox checkbox-sm"
              checked={createIsDisplay()}
              onChange={e => setCreateIsDisplay(e.currentTarget.checked)}
            />
            <span class="label-text">表示する</span>
          </label>

          <Show when={createError()}>{message => <p class="text-sm text-error">{message()}</p>}</Show>

          <button type="button" class="btn btn-primary w-full" disabled={creating()} onClick={() => void handleCreate()}>
            {creating() ? '作成中...' : '作成して追加'}
          </button>
        </div>
      </div>

      <div class="mt-6">
        <label class="label">選択中の Media</label>
        <Show
          when={props.entries().length > 0}
          fallback={<p class="text-sm text-base-content/60">Media はまだ追加されていません。</p>}
        >
          <div class="space-y-3">
            <For each={props.entries()}>
              {(entry, index) => (
                <div class="rounded-box border border-base-300 bg-base-100 p-4">
                  <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                      <p class="font-medium">{entry.title}</p>
                      <p class="text-xs text-base-content/60">{entry.typeName} / {entry.formatName}</p>
                      <a href={entry.url} target="_blank" rel="noreferrer" class="link link-hover break-all text-xs">
                        {entry.url}
                      </a>
                    </div>

                    <div class="flex items-center gap-2">
                      <button type="button" class="btn btn-ghost btn-xs" onClick={() => moveEntry(index(), -1)} disabled={index() === 0}>
                        ↑
                      </button>
                      <button
                        type="button"
                        class="btn btn-ghost btn-xs"
                        onClick={() => moveEntry(index(), 1)}
                        disabled={index() === props.entries().length - 1}
                      >
                        ↓
                      </button>
                      <button type="button" class="btn btn-ghost btn-xs text-error" onClick={() => removeEntry(index())}>
                        削除
                      </button>
                    </div>
                  </div>

                  <div class="mt-3 grid gap-3 md:grid-cols-[120px_1fr] md:items-center">
                    <div>
                      <label class="text-xs text-base-content/60">表示順</label>
                      <p class="mt-1 text-sm">{index() + 1}</p>
                    </div>

                    <div>
                      <label class="text-xs text-base-content/60">形式</label>
                      <p class="mt-1 text-sm">{entry.formatName}</p>
                    </div>
                  </div>
                </div>
              )}
            </For>
          </div>
        </Show>
      </div>
    </fieldset>
  );
};
