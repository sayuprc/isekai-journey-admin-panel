import { createSignal, For, onMount, Show } from 'solid-js';
import type { Creator, SongTag, SongType, SongTypeValue } from '../../generated';
import { client } from '../../utils/client';
import { createFormErrors } from '../../utils/form-error';
import { createSubmitting } from '../../utils/use-submitting';
import { setFlash } from '../Flash';
import { FormError } from '../FormError';
import { SearchableSelect } from '../SearchableSelect';

type CreatorEntry = {
  creatorId: string;
};

type SongTagEntry = {
  songTagId: string;
};

export const CreateForm = () => {
  const [creators, setCreators] = createSignal<Creator[]>([]);
  const [types, setTypes] = createSignal<SongType[]>([]);
  const [availableTags, setAvailableTags] = createSignal<SongTag[]>([]);

  const [lyricists, setLyricists] = createSignal<CreatorEntry[]>([]);
  const [composers, setComposers] = createSignal<CreatorEntry[]>([]);
  const [arrangers, setArrangers] = createSignal<CreatorEntry[]>([]);
  const [tags, setTags] = createSignal<SongTagEntry[]>([]);
  const [tagPickerValue, setTagPickerValue] = createSignal('');

  const { formError, getFieldError, clearErrors, handleError } = createFormErrors();
  const { isSubmitting, withSubmitting } = createSubmitting();

  const normalizeOptionalString = (value: FormDataEntryValue | null): string | null => {
    const normalized = value?.toString().trim() ?? '';

    return normalized === '' ? null : normalized;
  };

  onMount(async () => {
    const { data } = await client.api.songs['create-form'].get();
    if (data) {
      setCreators(data.creators);
      setTypes(data.types);
      setAvailableTags(data.tags);
    }
  });

  const addEntry = (setter: typeof setArrangers) => {
    setter(prev => [...prev, { creatorId: '' }]);
  };

  const removeEntry = (setter: typeof setArrangers, index: number) => {
    setter(prev => prev.filter((_, i) => i !== index));
  };

  const updateEntry = (setter: typeof setArrangers, index: number, field: keyof CreatorEntry, value: string) => {
    setter(prev => prev.map((entry, i) => (i === index ? { ...entry, [field]: value } : entry)));
  };

  const removeTagEntry = (index: number) => {
    setTags(prev => prev.filter((_, i) => i !== index));
  };

  const addTagEntry = (songTagId: string) => {
    if (songTagId === '') {
      return;
    }

    setTags(prev => (prev.some(entry => entry.songTagId === songTagId) ? prev : [...prev, { songTagId }]));
    setTagPickerValue('');
  };

  const handleSubmit = withSubmitting(async (e: Event) => {
    e.preventDefault();
    clearErrors();

    const form = e.target as HTMLFormElement;
    const formData = new FormData(form);

    const { data, error, status } = await client.api.songs.post({
      title: formData.get('title')?.toString() ?? '',
      description: formData.get('description')?.toString() ?? '',
      lyricsLink: normalizeOptionalString(formData.get('lyricsLink')),
      typeValue: Number(formData.get('typeValue')) as SongTypeValue,
      isDisplay: formData.get('isDisplay') === 'true',
      arrangers: arrangers(),
      composers: composers(),
      lyricists: lyricists(),
      tags: tags(),
    });

    if (data) {
      setFlash('作成しました');
      window.location.href = '/songs';
      return;
    }

    handleError(status, error);
  });

  const creatorOptions = (entries: CreatorEntry[], currentIndex: number) => {
    const selectedCreatorIds = new Set(
      entries.filter((entry, index) => index !== currentIndex && entry.creatorId !== '').map(entry => entry.creatorId),
    );

    return creators()
      .filter(creator => !selectedCreatorIds.has(creator.creatorId) || creator.creatorId === entries[currentIndex]?.creatorId)
      .map(creator => ({ value: creator.creatorId, label: creator.name }));
  };

  const tagOptions = () => {
    const selectedTagIds = new Set(tags().map(entry => entry.songTagId));

    return availableTags()
      .filter(tag => !selectedTagIds.has(tag.songTagId))
      .map(tag => ({ value: tag.songTagId, label: tag.name }));
  };

  const selectedTags = () =>
    tags()
      .map(entry => availableTags().find(tag => tag.songTagId === entry.songTagId))
      .filter((tag): tag is SongTag => tag !== undefined);

  const CreatorList = (props: { label: string; entries: () => CreatorEntry[]; setter: typeof setArrangers }) => (
    <div class="mt-4">
      <div class="flex items-center gap-2">
        <span class="label">{props.label}</span>
        <button type="button" class="btn btn-xs btn-outline" onclick={() => addEntry(props.setter)}>
          + 追加
        </button>
      </div>
      <For each={props.entries()}>
        {(entry, index) => (
          <div class="mt-2 flex items-center gap-3">
            <SearchableSelect
              options={creatorOptions(props.entries(), index())}
              value={entry.creatorId}
              onChange={value => updateEntry(props.setter, index(), 'creatorId', value)}
              placeholder="クリエイターを検索..."
              required
            />
            <button
              type="button"
              class="btn btn-ghost btn-xs btn-square text-error"
              onclick={() => removeEntry(props.setter, index())}
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="1.5"
                stroke="currentColor"
                class="size-4"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"
                />
              </svg>
            </button>
          </div>
        )}
      </For>
    </div>
  );

  const TagList = () => (
    <div class="mt-4">
      <div class="flex flex-col gap-2">
        <span class="label">楽曲タグ</span>
        <SearchableSelect
          options={tagOptions()}
          value={tagPickerValue()}
          onChange={value => addTagEntry(value)}
          placeholder="楽曲タグを検索して追加..."
        />
        <p class="text-xs text-base-content/60">選択したタグは下に追加されます。</p>
      </div>
      <Show
        when={selectedTags().length > 0}
        fallback={<p class="mt-3 text-sm text-base-content/60">タグはまだ追加されていません。</p>}
      >
        <div class="mt-3 flex flex-wrap gap-2">
          <For each={selectedTags()}>
            {(tag, index) => (
              <button
                type="button"
                class="badge badge-lg cursor-pointer gap-2 border border-base-300 bg-base-100 px-3 py-4"
                onclick={() => removeTagEntry(index())}
              >
                <span>{tag.name}</span>
                <span class="text-error" aria-hidden="true">
                  ×
                </span>
              </button>
            )}
          </For>
        </div>
      </Show>
    </div>
  );

  return (
    <form onsubmit={handleSubmit}>
      <a href="/songs" class="btn btn-ghost btn-sm mb-4">
        ← 一覧に戻る
      </a>
      <FormError message={formError()} onClose={clearErrors} />
      <div class="max-w-4xl space-y-6">
        <div class="grid gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
          <fieldset class="fieldset bg-base-200 border-base-300 rounded-box h-full border p-6">
            <legend class="px-2 text-sm font-semibold text-base-content/70">基本情報</legend>
            <div class="grid gap-5 md:grid-cols-2">
              <div>
                <label class="label">楽曲名</label>
                <input
                  type="text"
                  class="input w-full"
                  name="title"
                  required
                  classList={{ 'input-error': !!getFieldError('title') }}
                />
                <Show when={getFieldError('title')}>{message => <p class="mt-1 text-xs text-error">{message()}</p>}</Show>
              </div>

              <div>
                <label class="label">楽曲種別</label>
                <select class="select select-bordered w-full" name="typeValue" required>
                  <option value="" disabled selected>
                    選択してください
                  </option>
                  <For each={types()}>{type => <option value={type.value}>{type.name}</option>}</For>
                </select>
              </div>

              <div class="md:col-span-2">
                <label class="label">説明</label>
                <input
                  type="text"
                  class="input w-full"
                  name="description"
                  classList={{ 'input-error': !!getFieldError('description') }}
                />
                <Show when={getFieldError('description')}>
                  {message => <p class="mt-1 text-xs text-error">{message()}</p>}
                </Show>
              </div>

              <div class="md:col-span-2">
                <label class="label">歌詞リンク</label>
                <input
                  type="url"
                  class="input w-full"
                  name="lyricsLink"
                  placeholder="https://example.com/lyrics"
                  classList={{ 'input-error': !!getFieldError('lyricsLink') }}
                />
                <Show when={getFieldError('lyricsLink')}>
                  {message => <p class="mt-1 text-xs text-error">{message()}</p>}
                </Show>
              </div>

              <div>
                <label class="label">表示設定</label>
                <select class="select select-bordered w-full" name="isDisplay">
                  <option value="true" selected>
                    表示する
                  </option>
                  <option value="false">表示しない</option>
                </select>
              </div>

              <div class="md:col-span-2" />
            </div>
          </fieldset>

          <fieldset class="fieldset bg-base-200 border-base-300 rounded-box h-full border p-6">
            <legend class="px-2 text-sm font-semibold text-base-content/70">タグ</legend>
            <TagList />
          </fieldset>
        </div>

        <fieldset class="fieldset bg-base-200 border-base-300 rounded-box border p-6">
          <legend class="px-2 text-sm font-semibold text-base-content/70">関係者</legend>
          <CreatorList label="作詞者" entries={lyricists} setter={setLyricists} />
          <CreatorList label="作曲者" entries={composers} setter={setComposers} />
          <CreatorList label="編曲者" entries={arrangers} setter={setArrangers} />
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
