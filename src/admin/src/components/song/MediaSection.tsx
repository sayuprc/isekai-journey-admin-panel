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

type DisplayFilter = '' | 'true' | 'false';
type PerPage = 25 | 50 | 100;

const mediaFormatOptions: Array<{ value: MediaFormatValue; label: string }> = [
  { value: 1, label: 'MV' },
  { value: 2, label: '音源動画' },
  { value: 3, label: '配信アーカイブ' },
  { value: 4, label: 'ショート動画' },
  { value: 5, label: 'ライブ切り抜き' },
  { value: 99, label: 'その他' },
];

const mediaTypeOptions: Array<{ value: MediaTypeValue; label: string }> = [
  { value: 1, label: '動画' },
  { value: 2, label: '記事' },
  { value: 3, label: 'SNS投稿' },
  { value: 4, label: '公式ページ' },
  { value: 99, label: 'その他' },
];

const perPageOptions: PerPage[] = [25, 50, 100];

const mergeMedia = (current: Media[], incoming: Media[]): Media[] => {
  const map = new Map(current.map(item => [item.mediaId, item]));
  incoming.forEach(item => map.set(item.mediaId, item));

  return Array.from(map.values()).sort((a, b) => a.title.localeCompare(b.title, 'ja'));
};

const toErrorMessage = (error: unknown, fallback: string) => {
  if (typeof error === 'object' && error !== null && 'value' in error) {
    const value = (error as { value?: { message?: string; summary?: string; errors?: Array<{ message?: string }> } }).value;
    return value?.errors?.[0]?.message ?? value?.message ?? value?.summary ?? fallback;
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
  const [searchTypeValue, setSearchTypeValue] = createSignal<'' | `${MediaTypeValue}`>('');
  const [searchFormatValue, setSearchFormatValue] = createSignal<'' | `${MediaFormatValue}`>('');
  const [searchIsDisplay, setSearchIsDisplay] = createSignal<DisplayFilter>('');
  const [searchPage, setSearchPage] = createSignal(1);
  const [searchPerPage, setSearchPerPage] = createSignal<PerPage>(50);
  const [searchMaxPage, setSearchMaxPage] = createSignal(1);
  const [searching, setSearching] = createSignal(false);
  const [searchResults, setSearchResults] = createSignal<Media[]>([]);
  const [searchError, setSearchError] = createSignal<string | null>(null);
  const [hasSearched, setHasSearched] = createSignal(false);

  const [createTitle, setCreateTitle] = createSignal('');
  const [createUrl, setCreateUrl] = createSignal('');
  const [createTypeValue, setCreateTypeValue] = createSignal<MediaTypeValue>(1);
  const [createFormatValue, setCreateFormatValue] = createSignal<MediaFormatValue>(1);
  const [createIsDisplay, setCreateIsDisplay] = createSignal(true);
  const [creating, setCreating] = createSignal(false);
  const [createError, setCreateError] = createSignal<string | null>(null);

  const selectedIds = () => new Set(props.entries().map(entry => entry.mediaId));
  const allKnownMedia = () => mergeMedia(props.availableMedia(), searchResults());

  const normalize = (value: string) => value.trim().toLocaleLowerCase('ja');

  const buildMediaDetailUrl = (mediaId: string) => `/media/${mediaId}`;

  const duplicateCandidates = () => {
    const title = normalize(createTitle());
    const url = normalize(createUrl());

    if (title === '' && url === '') {
      return [];
    }

    return allKnownMedia()
      .map((item) => {
        const itemTitle = normalize(item.title);
        const itemUrl = normalize(item.url);
        const reasons: string[] = [];

        if (url !== '' && itemUrl === url) {
          reasons.push('同じURL');
        }

        if (title.length >= 3 && itemTitle !== '') {
          if (itemTitle === title) {
            reasons.push('同じタイトル');
          } else if (itemTitle.includes(title) || title.includes(itemTitle)) {
            reasons.push('類似タイトル');
          }
        }

        if (reasons.length === 0) {
          return null;
        }

        return { item, reasons };
      })
      .filter((entry): entry is { item: Media; reasons: string[] } => entry !== null)
      .slice(0, 5);
  };

  const hasExactUrlDuplicate = () => duplicateCandidates().some(entry => entry.reasons.includes('同じURL'));

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

  const handleSearch = async (page = 1) => {
    setSearchError(null);
    setSearching(true);
    setHasSearched(true);
    setSearchPage(page);

    const { data, error, status } = await client.api.media.search.get({
      query: {
        title: searchTitle(),
        type: searchTypeValue() || undefined,
        format: searchFormatValue() || undefined,
        is_display: searchIsDisplay() === '' ? undefined : searchIsDisplay() === 'true',
        page,
        per_page: searchPerPage(),
      },
    });

    setSearching(false);

    if (!data) {
      setSearchError(toErrorMessage(error, `検索に失敗しました (${status})`));
      return;
    }

    setSearchResults(data.media);
    setSearchMaxPage(data.maxPage);
    props.setAvailableMedia(prev => mergeMedia(prev, data.media));
  };

  const handleResetSearch = () => {
    setSearchTitle('');
    setSearchTypeValue('');
    setSearchFormatValue('');
    setSearchIsDisplay('');
    setSearchPage(1);
    setSearchPerPage(50);
    setSearchMaxPage(1);
    setSearchError(null);
    setHasSearched(false);
    setSearchResults([]);
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

    if (hasExactUrlDuplicate()) {
      setCreateError('同じURLの既存メディアがあります。既存メディアの追加を検討してください');
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

  const candidateResults = () => (hasSearched() ? searchResults() : props.availableMedia());

  return (
    <fieldset class="fieldset bg-base-200 border-base-300 rounded-box border p-6">
      <legend class="px-2 text-sm font-semibold text-base-content/70">メディア</legend>

      <div class="grid gap-6 lg:grid-cols-[minmax(0,1.3fr)_minmax(0,1fr)]">
        <div class="space-y-4">
          <div>
            <label class="label">既存メディアを検索</label>
            <div class="grid gap-3 md:grid-cols-2">
              <input
                type="text"
                class="input input-bordered w-full"
                value={searchTitle()}
                onInput={e => setSearchTitle(e.currentTarget.value)}
                placeholder="タイトルで検索"
              />

              <select
                class="select select-bordered w-full"
                value={searchTypeValue()}
                onChange={e => setSearchTypeValue(e.currentTarget.value as '' | `${MediaTypeValue}`)}
              >
                <option value="">すべての種別</option>
                <For each={mediaTypeOptions}>{option => <option value={option.value}>{option.label}</option>}</For>
              </select>

              <select
                class="select select-bordered w-full"
                value={searchFormatValue()}
                onChange={e => setSearchFormatValue(e.currentTarget.value as '' | `${MediaFormatValue}`)}
              >
                <option value="">すべての形式</option>
                <For each={mediaFormatOptions}>{option => <option value={option.value}>{option.label}</option>}</For>
              </select>

              <select
                class="select select-bordered w-full"
                value={searchIsDisplay()}
                onChange={e => setSearchIsDisplay(e.currentTarget.value as DisplayFilter)}
              >
                <option value="">すべての表示設定</option>
                <option value="true">表示する</option>
                <option value="false">表示しない</option>
              </select>

              <select
                class="select select-bordered w-full"
                value={searchPerPage()}
                onChange={e => setSearchPerPage(Number(e.currentTarget.value) as PerPage)}
              >
                <For each={perPageOptions}>{option => <option value={option}>{option}件表示</option>}</For>
              </select>
            </div>

            <div class="mt-3 flex gap-2">
              <button type="button" class="btn btn-outline" disabled={searching()} onClick={() => void handleSearch()}>
                {searching() ? '検索中...' : '検索'}
              </button>
              <button type="button" class="btn btn-ghost" disabled={searching()} onClick={handleResetSearch}>
                リセット
              </button>
            </div>
            <Show when={searchError()}>{message => <p class="mt-2 text-sm text-error">{message()}</p>}</Show>
          </div>

          <div class="space-y-2">
            <Show
              when={candidateResults().length > 0}
              fallback={<p class="text-sm text-base-content/60">{hasSearched() ? '条件に一致するメディアはありません。' : '候補のメディアはまだありません。'}</p>}
            >
              <div class="mb-2 flex items-center justify-between text-xs text-base-content/60">
                <p>{hasSearched() ? `${searchPage()} / ${searchMaxPage()} ページ` : `${candidateResults().length} 件の候補を表示中`}</p>
                <Show when={hasSearched() && searchMaxPage() > 1}>
                  <div class="flex gap-2">
                    <button
                      type="button"
                      class="btn btn-ghost btn-xs"
                      disabled={searching() || searchPage() <= 1}
                      onClick={() => void handleSearch(searchPage() - 1)}
                    >
                      前へ
                    </button>
                    <button
                      type="button"
                      class="btn btn-ghost btn-xs"
                      disabled={searching() || searchPage() >= searchMaxPage()}
                      onClick={() => void handleSearch(searchPage() + 1)}
                    >
                      次へ
                    </button>
                  </div>
                </Show>
              </div>
              <For each={candidateResults()}>
                {item => (
                  <div class="rounded-box border border-base-300 bg-base-100 p-3">
                    <div class="flex items-start justify-between gap-3">
                      <div class="min-w-0">
                        <div class="flex items-center gap-2">
                          <p class="truncate font-medium">{item.title}</p>
                          <Show when={selectedIds().has(item.mediaId)}>
                            <span class="badge badge-sm badge-primary badge-soft">選択中</span>
                          </Show>
                        </div>
                        <p class="text-xs text-base-content/60">{item.type.name} / {item.format.name}</p>
                        <a href={item.url} target="_blank" rel="noreferrer" class="link link-hover break-all text-xs">
                          {item.url}
                        </a>
                      </div>
                      <div class="flex flex-col items-end gap-2">
                        <a
                          href={buildMediaDetailUrl(item.mediaId)}
                          target="_blank"
                          rel="noreferrer"
                          class="btn btn-ghost btn-xs"
                        >
                          詳細
                        </a>
                        <button
                          type="button"
                          class="btn btn-xs btn-primary"
                          disabled={selectedIds().has(item.mediaId)}
                          onClick={() => addEntry(item)}
                        >
                          {selectedIds().has(item.mediaId) ? '追加済み' : '追加'}
                        </button>
                      </div>
                    </div>
                  </div>
                )}
              </For>
            </Show>
          </div>
        </div>

        <div class="space-y-3 rounded-box border border-base-300 bg-base-100 p-4">
          <div>
            <label class="label">新規メディア作成</label>
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

          <Show when={duplicateCandidates().length > 0}>
            <div class="rounded-box border border-warning/40 bg-warning/10 p-3">
              <p class="text-sm font-medium text-warning-content">重複候補があります</p>
              <div class="mt-2 space-y-2">
                <For each={duplicateCandidates()}>
                  {candidate => (
                    <div class="rounded-box bg-base-100 p-3">
                      <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                          <div class="flex items-center gap-2">
                            <p class="truncate text-sm font-medium">{candidate.item.title}</p>
                            <For each={candidate.reasons}>
                              {reason => <span class="badge badge-warning badge-sm badge-outline">{reason}</span>}
                            </For>
                          </div>
                          <p class="text-xs text-base-content/60">{candidate.item.type.name} / {candidate.item.format.name}</p>
                          <a href={candidate.item.url} target="_blank" rel="noreferrer" class="link link-hover break-all text-xs">
                            {candidate.item.url}
                          </a>
                        </div>
                        <div class="flex flex-col items-end gap-2">
                          <a
                            href={buildMediaDetailUrl(candidate.item.mediaId)}
                            target="_blank"
                            rel="noreferrer"
                            class="btn btn-ghost btn-xs"
                          >
                            詳細
                          </a>
                          <button type="button" class="btn btn-primary btn-xs" disabled={selectedIds().has(candidate.item.mediaId)} onClick={() => addEntry(candidate.item)}>
                            {selectedIds().has(candidate.item.mediaId) ? '追加済み' : '既存を追加'}
                          </button>
                        </div>
                      </div>
                    </div>
                  )}
                </For>
              </div>
            </div>
          </Show>

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
        <label class="label">選択中のメディア</label>
        <Show
          when={props.entries().length > 0}
          fallback={<p class="text-sm text-base-content/60">メディアはまだ追加されていません。</p>}
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
                      <a
                        href={buildMediaDetailUrl(entry.mediaId)}
                        target="_blank"
                        rel="noreferrer"
                        class="btn btn-ghost btn-xs"
                      >
                        詳細
                      </a>
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
