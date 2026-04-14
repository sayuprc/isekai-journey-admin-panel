import type { Song } from '../schemas/song';
import styles from '../styles/songTile.module.css';

interface SongTileProps {
  song: Song;
}

const clickWrapper = (e: Event) => {
  const wrapper = e.currentTarget as HTMLElement;
  const videoId = wrapper.getAttribute('data-video-id');
  const iframe = document.createElement('iframe');
  iframe.setAttribute('src', `https://www.youtube.com/embed/${videoId}?autoplay=1`);
  iframe.setAttribute('title', 'YouTube video player');
  iframe.setAttribute('frameborder', '0');
  iframe.setAttribute('allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share');
  iframe.setAttribute('allowfullscreen', '');
  iframe.setAttribute('class', 'youtube-embed');
  wrapper.innerHTML = '';
  wrapper.appendChild(iframe);
};

export const SongTile = (props: SongTileProps) => {
  const url = `https://img.youtube.com/vi/${props.song.videos[0].id}/mqdefault.jpg`;
  const songTypeClass = `song-type ${props.song.songType}`;

  return (
    <>
      <div class={styles.item}>
        <div class={styles.youtubeWrapper} data-video-id={props.song.videos[0].id} onClick={clickWrapper}>
          <img class={styles.embed} alt="Thumbnail" src={url} loading="lazy" />
        </div>
        <p class={styles.releasedOn}>
          {props.song.releasedOn}
          <span class={songTypeClass}>
            {props.song.songType.charAt(0).toUpperCase() + props.song.songType.slice(1).toLowerCase()}
          </span>
        </p>
        <h2 class={styles.title}>{props.song.title}</h2>
        <div class={styles.creators}>
          <p class={styles.creatorsList}>
            <span>作詞: {props.song.lyricists.map(user => user.name).join('・')}</span>
            <br />
            <span>作曲: {props.song.composers.map(user => user.name).join('・')}</span>
            <br />
            <span>編曲: {props.song.arrangers.map(user => user.name).join('・')}</span>
          </p>
        </div>
        <p class={styles.summary}>{props.song.summary}</p>
        <div class={styles.otherLinks}>
          {1 < props.song.videos.length && (
            <div>
              {props.song.videos.slice(1).map((video) => {
                const youtubeUrl = `https://www.youtube.com/watch?v=${video.id}`;

                return (
                  <a href={youtubeUrl} class={styles.videoLink} target="_blank">
                    <span class={styles.playIcon}>&#9654;</span>
                    {video.name}
                  </a>
                );
              })}
            </div>
          )}
        </div>
      </div>
    </>
  );
};
