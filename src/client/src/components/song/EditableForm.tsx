import { createSignal, For, onMount, Show } from 'solid-js';
import type { components } from '../../generated/schema';
import { client } from '../../utils/client';
import { setFlash } from '../Flash';
import { SearchableSelect } from '../SearchableSelect';

type Creator = components['schemas']['Creator'];
type SongType = components['schemas']['SongType'];
type SongTypeValue = components['schemas']['SongTypeValue'];

type CreatorEntry = {
  creatorId: string;
  orderNo: number;
};

interface Props {
  data?: { song: components['schemas']['Song'] };
  status: number;
}

export const EditableForm = (props: Props) => {
  const [creators, setCreators] = createSignal<Creator[]>([]);
  const [songTypes, setSongTypes] = createSignal<SongType[]>([]);

  const toEntries = (
    items: components['schemas']['Arranger'][] | undefined,
  ): CreatorEntry[] => (items ?? []).map(item => ({ creatorId: item.creatorId, orderNo: item.orderNo }));

  const [arrangers, setArrangers] = createSignal<CreatorEntry[]>(
    toEntries(props.data?.song.arrangers),
  );
  const [composers, setComposers] = createSignal<CreatorEntry[]>(
    toEntries(props.data?.song.composers),
  );
  const [lyricists, setLyricists] = createSignal<CreatorEntry[]>(
    toEntries(props.data?.song.lyricists),
  );

  onMount(async () => {
    const [creatorsRes, songTypesRes] = await Promise.all([
      client.api.creators.get(),
      client.api['song-types'].get(),
    ]);

    if (creatorsRes.data) {
      setCreators(creatorsRes.data.creators);
    }

    if (songTypesRes.data) {
      setSongTypes(songTypesRes.data.songTypes);
    }

    if (props.status === 404) {
      setFlash('データがない');
      window.location.href = '/songs';
    } else if (props.status === 422) {
      setFlash('リクエストがおかしい');
      window.location.href = '/songs';
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

  const handleSubmit = async (e: Event) => {
    e.preventDefault();
  };

  const handleDelete = async () => {
    if (!window.confirm('削除します。よろしいですか？')) {
      return;
    }

    const songId = props.data?.song.songId;

    if (!songId) {
      alert('削除対象の楽曲IDを取得できませんでした');
      return;
    }

    await client.api.songs({ songId: songId }).delete();

    setFlash('削除しました');
    window.location.href = '/songs';
  };

  const handleUpdate = async (e: Event) => {
    e.preventDefault();

    const form = (e.target as HTMLButtonElement).form as HTMLFormElement;
    const formData = new FormData(form);

    const songId = props.data?.song.songId;

    if (!songId) {
      alert('更新対象の楽曲IDを取得できませんでした');
      return;
    }

    const { data } = await client.api.songs({ songId: songId }).put({
      title: formData.get('title')?.toString() ?? '',
      description: formData.get('description')?.toString() ?? '',
      songTypeValue: Number(formData.get('songTypeValue')) as SongTypeValue,
      orderNo: Number(formData.get('orderNo')),
      arrangers: arrangers(),
      composers: composers(),
      lyricists: lyricists(),
    });

    if (data) {
      setFlash('更新しました');
      window.location.href = '/songs';
    }
  };

  const creatorOptions = () =>
    creators().map(creator => ({ value: creator.creatorId, label: creator.name }));

  const CreatorList = (listProps: {
    label: string;
    entries: () => CreatorEntry[];
    setter: typeof setArrangers;
  }) => (
    <div class="mt-4">
      <div class="flex items-center gap-2">
        <span class="label">{listProps.label}</span>
        <button
          type="button"
          class="btn btn-xs btn-outline"
          onclick={() => addEntry(listProps.setter)}
        >
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
                onchange={e =>
                  updateEntry(listProps.setter, index(), 'orderNo', Number(e.currentTarget.value))}
                required
                min="1"
              />
            </div>
            <button
              type="button"
              class="btn btn-ghost btn-xs btn-square text-error"
              onclick={() => removeEntry(listProps.setter, index())}
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
    <Show when={props.data} fallback={<p>読み込み中...</p>}>
      <a href="/songs" class="btn btn-ghost btn-sm mb-4">← 一覧に戻る</a>
      <form onsubmit={handleSubmit}>
        <fieldset class="fieldset bg-base-200 border-base-300 rounded-box max-w-lg border p-6">
          <label class="label">楽曲名</label>
          <input type="text" class="input w-full" name="title" required value={props.data?.song.title} />

          <label class="label">説明</label>
          <input
            type="text"
            class="input w-full"
            name="description"
            required
            value={props.data?.song.description}
          />

          <label class="label">楽曲種別</label>
          <select class="select select-bordered w-full" name="songTypeValue" required>
            <option value="" disabled>
              選択してください
            </option>
            <For each={songTypes()}>
              {songType => (
                <option
                  value={songType.value}
                  selected={songType.value === props.data?.song.songType.value}
                >
                  {songType.name}
                </option>
              )}
            </For>
          </select>

          <label class="label">表示順</label>
          <input
            type="number"
            class="input w-full"
            name="orderNo"
            required
            min="1"
            value={props.data?.song.orderNo}
          />

          <CreatorList label="作曲者" entries={composers} setter={setComposers} />
          <CreatorList label="作詞者" entries={lyricists} setter={setLyricists} />
          <CreatorList label="編曲者" entries={arrangers} setter={setArrangers} />

          <div class="mt-6 flex justify-end">
            <button onClick={handleUpdate} class="btn btn-primary">更新</button>
          </div>
        </fieldset>
      </form>

      <div class="divider max-w-lg" />

      <div class="max-w-lg rounded-box border border-error/20 bg-error/5 p-6">
        <h3 class="font-semibold text-error">危険な操作</h3>
        <p class="mt-1 text-sm text-base-content/60">この操作は取り消せません。</p>
        <div class="mt-4">
          <button onClick={handleDelete} class="btn btn-outline btn-error btn-sm">この楽曲を削除する</button>
        </div>
      </div>
    </Show>
  );
};
