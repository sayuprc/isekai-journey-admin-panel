import { createSignal, For, onMount, Show } from 'solid-js';
import type { components } from '../../generated/schema';
import { client } from '../../utils/client';
import { createFormErrors } from '../../utils/form-error';
import { setFlash } from '../Flash';
import { FormError } from '../FormError';
import { SearchableSelect } from '../SearchableSelect';

type Creator = components['schemas']['Creator'];
type SongType = components['schemas']['SongType'];
type SongTypeValue = components['schemas']['SongTypeValue'];
type SongAttribute = components['schemas']['SongAttribute'];
type SongAttributeValue = components['schemas']['SongAttributeValue'];

type CreatorEntry = {
  creatorId: string;
};

export const CreateForm = () => {
  const [creators, setCreators] = createSignal<Creator[]>([]);
  const [songTypes, setSongTypes] = createSignal<SongType[]>([]);
  const [attributes, setAttributes] = createSignal<SongAttribute[]>([]);

  const [lyricists, setLyricists] = createSignal<CreatorEntry[]>([]);
  const [composers, setComposers] = createSignal<CreatorEntry[]>([]);
  const [arrangers, setArrangers] = createSignal<CreatorEntry[]>([]);

  const { formError, getFieldError, clearErrors, handleError } = createFormErrors();

  onMount(async () => {
    const [creatorsRes, songTypesRes, attributesRes] = await Promise.all([
      client.api.creators.get(),
      client.api['song-types'].get(),
      client.api['song-attributes'].get(),
    ]);

    if (creatorsRes.data) {
      setCreators(creatorsRes.data.creators);
    }

    if (songTypesRes.data) {
      setSongTypes(songTypesRes.data.songTypes);
    }

    if (attributesRes.data) {
      setAttributes(attributesRes.data.attributes);
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

  const handleSubmit = async (e: Event) => {
    e.preventDefault();
    clearErrors();

    const form = e.target as HTMLFormElement;
    const formData = new FormData(form);

    const { data, error, status } = await client.api.songs.post({
      title: formData.get('title')?.toString() ?? '',
      description: formData.get('description')?.toString() ?? '',
      songTypeValue: Number(formData.get('songTypeValue')) as SongTypeValue,
      attributeValue: formData.get('attributeValue') !== ''
        ? Number(formData.get('attributeValue')) as SongAttributeValue
        : undefined,
      arrangers: arrangers(),
      composers: composers(),
      lyricists: lyricists(),
    });

    if (data) {
      setFlash('作成しました');
      window.location.href = '/songs';
      return;
    }

    handleError(status, error);
  };

  const creatorOptions = () =>
    creators().map(creator => ({ value: creator.creatorId, label: creator.name }));

  const CreatorList = (props: {
    label: string;
    entries: () => CreatorEntry[];
    setter: typeof setArrangers;
  }) => (
    <div class="mt-4">
      <div class="flex items-center gap-2">
        <span class="label">{props.label}</span>
        <button
          type="button"
          class="btn btn-xs btn-outline"
          onclick={() => addEntry(props.setter)}
        >
          + 追加
        </button>
      </div>
      <For each={props.entries()}>
        {(entry, index) => (
          <div class="mt-2 flex items-center gap-3">
            <SearchableSelect
              options={creatorOptions()}
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
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
              </svg>
            </button>
          </div>
        )}
      </For>
    </div>
  );

  return (
    <form onsubmit={handleSubmit}>
      <a href="/songs" class="btn btn-ghost btn-sm mb-4">← 一覧に戻る</a>
      <FormError message={formError()} onClose={clearErrors} />
      <fieldset class="fieldset bg-base-200 border-base-300 rounded-box max-w-lg border p-6">
        <label class="label">楽曲名</label>
        <input type="text" class="input w-full" name="title" required classList={{ 'input-error': !!getFieldError('title') }} />
        <Show when={getFieldError('title')}>
          {message => <p class="mt-1 text-xs text-error">{message()}</p>}
        </Show>

        <label class="label">説明</label>
        <input type="text" class="input w-full" name="description" required classList={{ 'input-error': !!getFieldError('description') }} />
        <Show when={getFieldError('description')}>
          {message => <p class="mt-1 text-xs text-error">{message()}</p>}
        </Show>

        <label class="label">楽曲種別</label>
        <select class="select select-bordered w-full" name="songTypeValue" required>
          <option value="" disabled selected>
            選択してください
          </option>
          <For each={songTypes()}>
            {songType => <option value={songType.value}>{songType.name}</option>}
          </For>
        </select>

        <label class="label">楽曲属性</label>
        <select class="select select-bordered w-full" name="attributeValue">
          <option value="" selected>
            選択してください
          </option>
          <For each={attributes()}>
            {attribute => <option value={attribute.value}>{attribute.name}</option>}
          </For>
        </select>

        <CreatorList label="作詞者" entries={lyricists} setter={setLyricists} />
        <CreatorList label="作曲者" entries={composers} setter={setComposers} />
        <CreatorList label="編曲者" entries={arrangers} setter={setArrangers} />

        <div class="mt-6 flex justify-end">
          <button class="btn btn-primary">作成</button>
        </div>
      </fieldset>
    </form>
  );
};
