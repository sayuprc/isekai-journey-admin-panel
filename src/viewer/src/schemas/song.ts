export interface Song {
  title: string;
  summary: string;
  releasedOn: string;
  videos: Video[];
  songType: SongType;
  lyricists: Lyricist[];
  composers: Composer[];
  arrangers: Arranger[];
}

export interface Video {
  id: string;
  name: string;
}

export interface Lyricist {
  name: string;
}

export interface Composer {
  name: string;
}

export interface Arranger {
  name: string;
}

export type SongType = 'original' | 'unit' | 'cover' | 'collaboration';
