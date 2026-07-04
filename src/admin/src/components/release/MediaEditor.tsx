import { For, Index, Show, createMemo, createSignal } from 'solid-js';
import type { MediumFormatValue, ReleaseGetResponse, SongSummary } from '../../generated';
import { client } from '../../utils/client';

export const MEDIUM_FORMAT_OPTIONS: Array<{ value: MediumFormatValue; label: string }> = [
  { value: 1, label: '配信' },
  { value: 2, label: 'CD' },
  { value: 3, label: 'DVD' },
  { value: 4, label: 'Blu-ray' },
  { value: 99, label: 'その他' },
];

export type TrackForm = {
  songId: string;
  title: string;
};

export type MediumForm = {
  formatValue: MediumFormatValue;
  tracks: TrackForm[];
};

/** API レスポンスからフォーム状態を組み立てる（楽曲名は収録曲 read model から引く）。 */
export const toMediumForms = (data: ReleaseGetResponse): MediumForm[] => {
  const titleBySongId = new Map(data.songs.map(song => [song.songId, song.title]));

  return data.release.media.map(medium => ({
    formatValue: medium.formatValue,
    tracks: medium.tracks.map(track => ({
      songId: track.songId,
      title: titleBySongId.get(track.songId) ?? track.songId,
    })),
  }));
};

/** フォーム状態を API の media リクエスト形へ変換する（position / trackNo は並び順から採番）。 */
export const toMediaPayload = (media: MediumForm[]) =>
  media.map((medium, mediumIndex) => ({
    position: mediumIndex + 1,
    formatValue: medium.formatValue,
    tracks: medium.tracks.map((track, trackIndex) => ({
      songId: track.songId,
      trackNo: trackIndex + 1,
    })),
  }));

interface MediaEditorProps {
  media: MediumForm[];
  onChange: (updater: (prev: MediumForm[]) => MediumForm[]) => void;
  fieldError?: string;
}

export const MediaEditor = (props: MediaEditorProps) => {
  const [searchTitle, setSearchTitle] = createSignal('');
  const [searchResults, setSearchResults] = createSignal<SongSummary[]>([]);
  const [searchError, setSearchError] = createSignal<string | null>(null);
  const [isSearching, setIsSearching] = createSignal(false);
  const [hasSearched, setHasSearched] = createSignal(false);
  const [targetMediumIndex, setTargetMediumIndex] = createSignal(0);

  // 楽曲は媒体をまたいでもリリース全体で 1 回まで。
  const selectedSongIds = createMemo(
    () => new Set(props.media.flatMap(medium => medium.tracks.map(track => track.songId))),
  );

  const addMedium = () => {
    props.onChange(prev => [...prev, { formatValue: 1, tracks: [] }]);
  };

  const removeMedium = (index: number) => {
    props.onChange(prev => prev.filter((_, i) => i !== index));
    setTargetMediumIndex(0);
  };

  const moveMedium = (index: number, direction: -1 | 1) => {
    props.onChange((prev) => {
      const nextIndex = index + direction;
      if (nextIndex < 0 || nextIndex >= prev.length) {
        return prev;
      }

      const cloned = [...prev];
      const [medium] = cloned.splice(index, 1);
      if (!medium) {
        return prev;
      }
      cloned.splice(nextIndex, 0, medium);

      return cloned;
    });
  };

  const setFormat = (index: number, formatValue: MediumFormatValue) => {
    props.onChange(prev => prev.map((medium, i) => (i === index ? { ...medium, formatValue } : medium)));
  };

  const addTrack = (song: SongSummary) => {
    if (selectedSongIds().has(song.songId)) {
      return;
    }

    const index = Math.min(targetMediumIndex(), props.media.length - 1);

    if (index < 0) {
      return;
    }

    props.onChange(prev =>
      prev.map((medium, i) =>
        i === index
          ? { ...medium, tracks: [...medium.tracks, { songId: song.songId, title: song.title }] }
          : medium,
      ));
  };

  const removeTrack = (mediumIndex: number, songId: string) => {
    props.onChange(prev =>
      prev.map((medium, i) =>
        i === mediumIndex
          ? { ...medium, tracks: medium.tracks.filter(track => track.songId !== songId) }
          : medium,
      ));
  };

  const moveTrack = (mediumIndex: number, trackIndex: number, direction: -1 | 1) => {
    props.onChange(prev =>
      prev.map((medium, i) => {
        if (i !== mediumIndex) {
          return medium;
        }

        const nextIndex = trackIndex + direction;
        if (nextIndex < 0 || nextIndex >= medium.tracks.length) {
          return medium;
        }

        const cloned = [...medium.tracks];
        const [track] = cloned.splice(trackIndex, 1);
        if (!track) {
          return medium;
        }
        cloned.splice(nextIndex, 0, track);

        return { ...medium, tracks: cloned };
      }));
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

  return (
    <fieldset class="rounded-box border border-base-300 bg-base-200 p-6">
      <legend class="px-2 text-sm font-semibold text-base-content/70">媒体と収録楽曲</legend>
      <Show when={props.fieldError}>{message => <p class="mb-4 text-sm text-error">{message()}</p>}</Show>
      <div class="space-y-6">
        <Show
          when={props.media.length > 0}
          fallback={<p class="text-sm text-base-content/60">媒体はまだ登録されていません。</p>}
        >
          <Index each={props.media}>
            {(medium, mediumIndex) => (
              <div class="rounded-box border border-base-300 bg-base-100 p-4">
                <div class="flex flex-wrap items-end justify-between gap-4">
                  <div class="flex items-end gap-4">
                    <span class="badge badge-neutral badge-sm mb-2">媒体 {mediumIndex + 1}</span>
                    <div>
                      <label class="label">媒体種別</label>
                      <select
                        class="select select-bordered select-sm"
                        value={String(medium().formatValue)}
                        onChange={e => setFormat(mediumIndex, Number(e.currentTarget.value) as MediumFormatValue)}
                      >
                        <For each={MEDIUM_FORMAT_OPTIONS}>
                          {option => <option value={option.value}>{option.label}</option>}
                        </For>
                      </select>
                    </div>
                  </div>
                  <div class="flex gap-2">
                    <button
                      type="button"
                      class="btn btn-ghost btn-xs"
                      disabled={mediumIndex === 0}
                      onClick={() => moveMedium(mediumIndex, -1)}
                    >
                      ↑
                    </button>
                    <button
                      type="button"
                      class="btn btn-ghost btn-xs"
                      disabled={mediumIndex === props.media.length - 1}
                      onClick={() => moveMedium(mediumIndex, 1)}
                    >
                      ↓
                    </button>
                    <button
                      type="button"
                      class="btn btn-outline btn-error btn-xs"
                      onClick={() => removeMedium(mediumIndex)}
                    >
                      媒体を削除
                    </button>
                  </div>
                </div>

                <div class="mt-4">
                  <Show
                    when={medium().tracks.length > 0}
                    fallback={<p class="text-sm text-base-content/60">収録楽曲はまだ登録されていません。</p>}
                  >
                    <div class="overflow-x-auto rounded-box border border-base-300">
                      <table class="table table-sm">
                        <thead>
                          <tr>
                            <th>曲順</th>
                            <th>楽曲名</th>
                            <th class="text-right">操作</th>
                          </tr>
                        </thead>
                        <tbody>
                          <For each={medium().tracks}>
                            {(track, trackIndex) => (
                              <tr>
                                <td>{trackIndex() + 1}</td>
                                <td>{track.title}</td>
                                <td>
                                  <div class="flex justify-end gap-2">
                                    <button
                                      type="button"
                                      class="btn btn-ghost btn-xs"
                                      disabled={trackIndex() === 0}
                                      onClick={() => moveTrack(mediumIndex, trackIndex(), -1)}
                                    >
                                      ↑
                                    </button>
                                    <button
                                      type="button"
                                      class="btn btn-ghost btn-xs"
                                      disabled={trackIndex() === medium().tracks.length - 1}
                                      onClick={() => moveTrack(mediumIndex, trackIndex(), 1)}
                                    >
                                      ↓
                                    </button>
                                    <a href={`/songs/${track.songId}`} class="btn btn-ghost btn-xs">
                                      楽曲を見る
                                    </a>
                                    <button
                                      type="button"
                                      class="btn btn-outline btn-error btn-xs"
                                      onClick={() => removeTrack(mediumIndex, track.songId)}
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
              </div>
            )}
          </Index>
        </Show>

        <div>
          <button type="button" class="btn btn-outline btn-sm" onClick={addMedium}>
            媒体を追加
          </button>
        </div>

        <Show when={props.media.length > 0}>
          <div>
            <label class="label">楽曲を追加</label>
            <div class="flex flex-col gap-4 md:flex-row md:items-end">
              <div>
                <label class="label">追加先媒体</label>
                <select
                  class="select select-bordered select-sm"
                  value={String(Math.min(targetMediumIndex(), props.media.length - 1))}
                  onChange={e => setTargetMediumIndex(Number(e.currentTarget.value))}
                >
                  <Index each={props.media}>
                    {(medium, index) => (
                      <option value={index}>
                        媒体
                        {' '}
                        {index + 1}
                        （
                        {MEDIUM_FORMAT_OPTIONS.find(option => option.value === medium().formatValue)?.label ?? '不明'}
                        ）
                      </option>
                    )}
                  </Index>
                </select>
              </div>
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
              <button type="button" class="btn btn-primary" disabled={isSearching()} onClick={handleSongSearch}>
                {isSearching() ? '検索中...' : '検索'}
              </button>
            </div>

            <Show when={searchError()}>{message => <p class="mt-3 text-sm text-error">{message()}</p>}</Show>

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
                      fallback={(
                        <tr>
                          <td colSpan={4} class="text-center text-sm text-base-content/60">
                            条件に一致する楽曲はありません。
                          </td>
                        </tr>
                      )}
                    >
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
                                onClick={() => addTrack(song)}
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
        </Show>
      </div>
    </fieldset>
  );
};
