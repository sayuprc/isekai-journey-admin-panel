import { createSignal, For, onMount } from 'solid-js';
import type { components } from '../../generated/schema';
import { client } from '../../utils/client';
import { setFlash } from '../Flash';

type Creator = components['schemas']['Creator'];
type SongType = components['schemas']['SongType'];
// type SongTypeValue = components['schemas']['SongTypeValue'];

type CreatorEntry = {
  creatorId: string;
};

export const CreateForm = () => {
  const [creators, setCreators] = createSignal<Creator[]>([]);
  const [songTypes, setSongTypes] = createSignal<SongType[]>([]);

  const [arrangers, setArrangers] = createSignal<CreatorEntry[]>([]);
  const [composers, setComposers] = createSignal<CreatorEntry[]>([]);
  const [lyricists, setLyricists] = createSignal<CreatorEntry[]>([]);

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

    const form = e.target as HTMLFormElement;
    const formData = new FormData(form);

    const { data } = await client.api.songs.post({
      title: formData.get('title')?.toString() ?? '',
      description: formData.get('description')?.toString() ?? '',
      songTypeValue: Number(formData.get('songTypeValue')),
      arrangers: arrangers(),
      composers: composers(),
      lyricists: lyricists(),
    });

    if (data) {
      setFlash('作成しました');
      window.location.href = '/songs';
    }
  };

  const CreatorList = (props: {
    label: string;
    entries: () => CreatorEntry[];
    setter: typeof setArrangers;
  }) => (
    <div class="mt-2">
      <div class="flex items-center gap-2">
        <label class="label">{props.label}</label>
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
          <div class="mt-1 flex items-end gap-2">
            <div>
              <label class="label text-xs">クリエイター</label>
              <select
                class="select select-bordered"
                value={entry.creatorId}
                onchange={e =>
                  updateEntry(props.setter, index(), 'creatorId', e.currentTarget.value)}
                required
              >
                <option value="" disabled>
                  選択してください
                </option>
                <For each={creators()}>
                  {creator => (
                    <option value={creator.creatorId}>{creator.name}</option>
                  )}
                </For>
              </select>
            </div>
            <button
              type="button"
              class="btn btn-xs btn-error"
              onclick={() => removeEntry(props.setter, index())}
            >
              削除
            </button>
          </div>
        )}
      </For>
    </div>
  );

  return (
    <form onsubmit={handleSubmit}>
      <fieldset class="fieldset bg-base-200 border-base-300 rounded-box border p-4">
        <legend class="fieldset-legend">楽曲作成</legend>

        <label class="label">楽曲名</label>
        <input type="text" class="input" name="title" required />

        <label class="label">説明</label>
        <input type="text" class="input" name="description" required />

        <label class="label">楽曲種別</label>
        <select class="select select-bordered" name="songTypeValue" required>
          <option value="" disabled selected>
            選択してください
          </option>
          <For each={songTypes()}>
            {songType => <option value={songType.value}>{songType.name}</option>}
          </For>
        </select>

        <CreatorList label="作曲者" entries={composers} setter={setComposers} />
        <CreatorList label="作詞者" entries={lyricists} setter={setLyricists} />
        <CreatorList label="編曲者" entries={arrangers} setter={setArrangers} />

        <button class="btn btn-neutral mt-4">作成</button>
      </fieldset>
    </form>
  );
};
