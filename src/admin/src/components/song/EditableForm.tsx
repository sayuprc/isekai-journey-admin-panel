import { createEffect, createSignal, For, Show } from 'solid-js';
import type { Arranger, Creator, Song, SongTag, SongType, SongTypeValue } from '../../generated';
import { client } from '../../utils/client';
import { createFormErrors } from '../../utils/form-error';
import { createSubmitting } from '../../utils/use-submitting';
import { setFlash } from '../Flash';
import { FormError } from '../FormError';
import { SearchableSelect } from '../SearchableSelect';

type CreatorEntry = {
  creatorId: string;
  orderNo: number;
};

type SongTagEntry = {
  songTagId: string;
};

interface Props {
  data?: { song: Song; creators: Creator[]; types: SongType[]; tags: SongTag[] };
  status: number;
}

export const EditableForm = (props: Props) => {
  const back = new URLSearchParams(window.location.search).get('back') ?? '';
  const listQuery = (() => {
    if (!back.startsWith('?')) return '';
    try {
      const q = new URLSearchParams(back.slice(1)).toString();
      return q ? `?${q}` : '';
    } catch {
      return '';
    }
  })();
  const listUrl = `/songs${listQuery}`;

  const creators = props.data?.creators ?? [];
  const types = props.data?.types ?? [];
  const availableTags = props.data?.tags ?? [];

  const toEntries = (items: Arranger[] | undefined): CreatorEntry[] =>
    (items ?? []).map(item => ({ creatorId: item.creatorId, orderNo: item.orderNo }));

  const [lyricists, setLyricists] = createSignal<CreatorEntry[]>(toEntries(props.data?.song.lyricists));
  const [composers, setComposers] = createSignal<CreatorEntry[]>(toEntries(props.data?.song.composers));
  const [arrangers, setArrangers] = createSignal<CreatorEntry[]>(toEntries(props.data?.song.arrangers));
  const [tags, setTags] = createSignal<SongTagEntry[]>(
    (props.data?.song.tags ?? []).map(tag => ({ songTagId: tag.songTagId })),
  );

  const { formError, setFormError, getFieldError, clearErrors, handleError } = createFormErrors();
  const { isSubmitting, withSubmitting } = createSubmitting();

  createEffect(() => {
    if (props.status === 404) {
      setFlash('データがない');
      window.location.href = listUrl;
    } else if (props.status === 422) {
      setFlash('リクエストがおかしい');
      window.location.href = listUrl;
    }
  });

  const addEntry = (setter: typeof setArrangers) => {
    setter(prev => [...prev, { creatorId: '', orderNo: prev.length + 1 }]);
  };

  const removeEntry = (setter: typeof setArrangers, index: number) => {
    setter(prev => prev.filter((_, i) => i !== index));
  };

  const updateEntry = (
    setter: typeof setArrangers,
    index: number,
    field: keyof CreatorEntry,
    value: string | number,
  ) => {
    setter(prev => prev.map((entry, i) => (i === index ? { ...entry, [field]: value } : entry)));
  };

  const addTagEntry = () => {
    setTags(prev => [...prev, { songTagId: '' }]);
  };

  const removeTagEntry = (index: number) => {
    setTags(prev => prev.filter((_, i) => i !== index));
  };

  const updateTagEntry = (index: number, value: string) => {
    setTags(prev => prev.map((entry, i) => (i === index ? { ...entry, songTagId: value } : entry)));
  };

  const handleSubmit = async (e: Event) => {
    e.preventDefault();
  };

  const handleDelete = withSubmitting(async () => {
    if (!window.confirm('削除します。よろしいですか？')) {
      return;
    }

    clearErrors();

    const songId = props.data?.song.songId;

    if (!songId) {
      setFormError('削除対象の楽曲IDを取得できませんでした');
      return;
    }

    const { error, status } = await client.api.songs({ songId: songId }).delete();

    if (error) {
      handleError(status, error);
      return;
    }

    setFlash('削除しました');
    window.location.href = listUrl;
  });

  const handleUpdate = withSubmitting(async (e: Event) => {
    e.preventDefault();
    clearErrors();

    const form = (e.target as HTMLButtonElement).form as HTMLFormElement;
    const formData = new FormData(form);

    const songId = props.data?.song.songId;

    if (!songId) {
      setFormError('更新対象の楽曲IDを取得できませんでした');
      return;
    }

    const { data, error, status } = await client.api.songs({ songId: songId }).put({
      title: formData.get('title')?.toString() ?? '',
      description: formData.get('description')?.toString() ?? '',
      typeValue: Number(formData.get('typeValue')) as SongTypeValue,
      isDisplay: formData.get('isDisplay') === 'true',
      orderNo: Number(formData.get('orderNo')),
      arrangers: arrangers(),
      composers: composers(),
      lyricists: lyricists(),
      tags: tags(),
    });

    if (data) {
      setFlash('更新しました');
      window.location.href = listUrl;
      return;
    }

    if (status === 404) {
      setFlash('データがありません');
      window.location.href = listUrl;
      return;
    }

    handleError(status, error);
  });

  const creatorOptions = () => creators.map(creator => ({ value: creator.creatorId, label: creator.name }));
  const tagOptions = () => availableTags.map(tag => ({ value: tag.songTagId, label: tag.name }));

  const CreatorList = (listProps: { label: string; entries: () => CreatorEntry[]; setter: typeof setArrangers }) => (
    <div class="mt-4">
      <div class="flex items-center gap-2">
        <span class="label">{listProps.label}</span>
        <button type="button" class="btn btn-xs btn-outline" onclick={() => addEntry(listProps.setter)}>
          + 追加
        </button>
      </div>
      <For each={listProps.entries()}>
        {(entry, index) => (
          <div class="mt-2 flex items-center gap-3">
            <SearchableSelect
              options={creatorOptions()}
              value={entry.creatorId}
              onChange={value => updateEntry(listProps.setter, index(), 'creatorId', value)}
              placeholder="クリエイターを検索..."
              required
            />
            <div class="flex items-center gap-2">
              <label class="text-xs text-base-content/60">順</label>
              <input
                type="number"
                class="input input-bordered w-16"
                value={entry.orderNo}
                onchange={e => updateEntry(listProps.setter, index(), 'orderNo', Number(e.currentTarget.value))}
                required
                min="1"
              />
            </div>
            <button
              type="button"
              class="btn btn-ghost btn-xs btn-square text-error"
              onclick={() => removeEntry(listProps.setter, index())}
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
      <div class="flex items-center gap-2">
        <span class="label">楽曲タグ</span>
        <button type="button" class="btn btn-xs btn-outline" onclick={addTagEntry}>
          + 追加
        </button>
      </div>
      <For each={tags()}>
        {(entry, index) => (
          <div class="mt-2 flex items-center gap-3">
            <SearchableSelect
              options={tagOptions()}
              value={entry.songTagId}
              onChange={value => updateTagEntry(index(), value)}
              placeholder="楽曲タグを検索..."
              required
            />
            <button
              type="button"
              class="btn btn-ghost btn-xs btn-square text-error"
              onclick={() => removeTagEntry(index())}
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

  return (
    <Show when={props.data} fallback={<p>読み込み中...</p>}>
      <a href={listUrl} class="btn btn-ghost btn-sm mb-4">
        ← 一覧に戻る
      </a>
      <FormError message={formError()} onClose={clearErrors} />
      <form onsubmit={handleSubmit}>
        <fieldset class="fieldset bg-base-200 border-base-300 rounded-box max-w-lg border p-6">
          <label class="label">楽曲名</label>
          <input
            type="text"
            class="input w-full"
            name="title"
            required
            value={props.data?.song.title}
            classList={{ 'input-error': !!getFieldError('title') }}
          />
          <Show when={getFieldError('title')}>{message => <p class="mt-1 text-xs text-error">{message()}</p>}</Show>

          <label class="label">説明</label>
          <input
            type="text"
            class="input w-full"
            name="description"
            value={props.data?.song.description}
            classList={{ 'input-error': !!getFieldError('description') }}
          />
          <Show when={getFieldError('description')}>
            {message => <p class="mt-1 text-xs text-error">{message()}</p>}
          </Show>

          <label class="label">楽曲種別</label>
          <select class="select select-bordered w-full" name="typeValue" required>
            <option value="" disabled>
              選択してください
            </option>
            <For each={types}>
              {type => (
                <option value={type.value} selected={type.value === props.data?.song.type.value}>
                  {type.name}
                </option>
              )}
            </For>
          </select>

          <div class="flex gap-4">
            <div class="flex flex-1 flex-col">
              <label class="label">表示設定</label>
              <select class="select select-bordered w-full" name="isDisplay">
                <option value="true" selected={props.data?.song.isDisplay === true}>
                  表示する
                </option>
                <option value="false" selected={props.data?.song.isDisplay === false}>
                  表示しない
                </option>
              </select>
            </div>
            <div class="flex flex-1 flex-col">
              <label class="label">表示順</label>
              <input type="number" class="input w-full" name="orderNo" required min="1" value={props.data?.song.orderNo} />
            </div>
          </div>

          <CreatorList label="作詞者" entries={lyricists} setter={setLyricists} />
          <CreatorList label="作曲者" entries={composers} setter={setComposers} />
          <CreatorList label="編曲者" entries={arrangers} setter={setArrangers} />
          <TagList />

          <div class="mt-6 flex justify-end">
            <button onClick={handleUpdate} class="btn btn-primary" disabled={isSubmitting()}>
              {isSubmitting() ? '更新中...' : '更新'}
            </button>
          </div>
        </fieldset>
      </form>

      <div class="divider max-w-lg" />

      <div class="max-w-lg rounded-box border border-error/20 bg-error/5 p-6">
        <h3 class="font-semibold text-error">危険な操作</h3>
        <p class="mt-1 text-sm text-base-content/60">この操作は取り消せません。</p>
        <div class="mt-4">
          <button onClick={handleDelete} class="btn btn-outline btn-error btn-sm" disabled={isSubmitting()}>
            {isSubmitting() ? '削除中...' : 'この楽曲を削除する'}
          </button>
        </div>
      </div>
    </Show>
  );
};
