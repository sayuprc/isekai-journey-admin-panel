import { createSignal, For } from 'solid-js';
import type { Song, SongType } from '../schemas/song';
import { SongTile } from './SongTile';

interface SongListProps {
  songs: Song[];
}

export const SongList = (props: SongListProps) => {
  const filterSongs = () => {
    const search = searchValue().toLocaleLowerCase();
    const songType = songTypeValue();

    return props.songs.filter((song) => {
      const searchCondition = search !== '' && song.title.toLocaleLowerCase().includes(search);
      const songTypeCondition = songType !== 'all' && song.songType === songType;

      return (search === '' && songType === 'all')
        || (search !== '' && songType === 'all' && searchCondition)
        || (search === '' && songType !== 'all' && songTypeCondition)
        || (search !== '' && songType !== 'all' && searchCondition && songTypeCondition);
    });
  };

  const orderByReleasedOn = () => {
    const order = songOrderValue();

    return filterSongs().sort((a, b) => {
      return order === 'asc'
        ? a.releasedOn.localeCompare(b.releasedOn)
        : b.releasedOn.localeCompare(a.releasedOn);
    });
  };

  return (
    <>
      <For each={orderByReleasedOn()}>
        {song => <SongTile song={song} />}
      </For>
    </>
  );
};

type SongOrderSelectType = 'asc' | 'desc';

const [songOrderValue, setSongOrderValue] = createSignal<SongOrderSelectType>('desc');

export const SongOrderSelect = () => {
  const handleChange = (e: Event) => {
    const value = (e.currentTarget as HTMLSelectElement).value;

    if (value === 'asc' || value === 'desc') {
      setSongOrderValue(value);
    }
  };

  return (
    <>
      <select onChange={handleChange}>
        <option value="desc">新しい順</option>
        <option value="asc">古い順</option>
      </select>
    </>
  );
};

type SongTypes = 'all' | SongType;

const [songTypeValue, setSongTypeValue] = createSignal<SongTypes>('all');

export const SongTypeSelect = () => {
  const handleChange = (e: Event) => {
    const value = (e.currentTarget as HTMLSelectElement).value;
    if (value === 'all' || value === 'original' || value === 'unit' || value === 'cover' || value === 'collaboration') {
      setSongTypeValue(value);
    }
  };

  return (
    <>
      <select onChange={handleChange}>
        <option value="all">All</option>
        <option value="original">Original</option>
        <option value="unit">Unit</option>
        <option value="cover">Cover</option>
        <option value="collaboration">Collaboration</option>
      </select>
    </>
  );
};

const [searchValue, setSearchValue] = createSignal('');

export const SearchInput = () => {
  const handleChange = (e: Event) => {
    setSearchValue((e.currentTarget as HTMLInputElement).value);
  };

  return (
    <>
      <input type="text" value={searchValue()} onInput={handleChange} />
    </>
  );
};
