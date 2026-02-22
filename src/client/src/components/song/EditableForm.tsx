import { createSignal, For, onMount, Show } from 'solid-js';
import type { components } from '../../generated/schema';
import { client } from '../../utils/client';
import { setFlash } from '../Flash';

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
      client.GET('/creators'),
      client.GET('/song-types'),
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

    await client.DELETE('/songs/{songId}', {
      params: {
        path: {
          songId: songId,
        },
      },
    });

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

    const { data } = await client.PUT('/songs/{songId}', {
      params: {
        path: {
          songId: songId,
        },
      },
      body: {
        title: formData.get('title')?.toString() ?? '',
        description: formData.get('description')?.toString() ?? '',
        songTypeValue: Number(formData.get('songTypeValue')) as SongTypeValue,
        orderNo: Number(formData.get('orderNo')),
        arrangers: arrangers(),
        composers: composers(),
        lyricists: lyricists(),
      },
    });

    if (data) {
      setFlash('更新しました');
      window.location.href = '/songs';
    }
  };

  const CreatorList = (listProps: {
    label: string;
    entries: () => CreatorEntry[];
    setter: typeof setArrangers;
  }) => (
    <div class="mt-2">
      <div class="flex items-center gap-2">
        <label class="label">{listProps.label}</label>
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
          <div class="mt-1 flex items-end gap-2">
            <div>
              <label class="label text-xs">クリエイター</label>
              <select
                class="select select-bordered"
                onchange={e =>
                  updateEntry(listProps.setter, index(), 'creatorId', e.currentTarget.value)}
                required
              >
                <option value="" disabled selected={entry.creatorId === ''}>
                  選択してください
                </option>
                <For each={creators()}>
                  {creator => (
                    <option
                      value={creator.creatorId}
                      selected={creator.creatorId === entry.creatorId}
                    >
                      {creator.name}
                    </option>
                  )}
                </For>
              </select>
            </div>
            <div>
              <label class="label text-xs">表示順</label>
              <input
                type="number"
                class="input input-bordered w-20"
                value={entry.orderNo}
                onchange={e =>
                  updateEntry(listProps.setter, index(), 'orderNo', Number(e.currentTarget.value))}
                required
                min="1"
              />
            </div>
            <button
              type="button"
              class="btn btn-xs btn-error"
              onclick={() => removeEntry(listProps.setter, index())}
            >
              削除
            </button>
          </div>
        )}
      </For>
    </div>
  );

  return (
    <Show when={props.data} fallback={<p>読み込み中...</p>}>
      <form onsubmit={handleSubmit}>
        <fieldset class="fieldset bg-base-200 border-base-300 rounded-box border p-4">
          <legend class="fieldset-legend">楽曲詳細</legend>

          <label class="label">楽曲名</label>
          <input type="text" class="input" name="title" required value={props.data?.song.title} />

          <label class="label">説明</label>
          <input
            type="text"
            class="input"
            name="description"
            required
            value={props.data?.song.description}
          />

          <label class="label">楽曲種別</label>
          <select class="select select-bordered" name="songTypeValue" required>
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
            class="input"
            name="orderNo"
            required
            min="1"
            value={props.data?.song.orderNo}
          />

          <CreatorList label="作曲者" entries={composers} setter={setComposers} />
          <CreatorList label="作詞者" entries={lyricists} setter={setLyricists} />
          <CreatorList label="編曲者" entries={arrangers} setter={setArrangers} />

          <div class="flex justify-between gap-2">
            <button onClick={handleDelete} class="btn btn-error mt-4">削除</button>
            <button onClick={handleUpdate} class="btn btn-neutral mt-4">更新</button>
          </div>
        </fieldset>
      </form>
    </Show>
  );
};
