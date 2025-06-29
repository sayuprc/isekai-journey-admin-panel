import { onMount, Show } from 'solid-js';
import type { components } from '../../generated/schema';
import { setFlash } from '../Flash';

interface Props {
  data?: { songType: components['schemas']['SongType'] };
  status: number;
}

export const EditableForm = (props: Props) => {
  const handleSubmit = async (e: Event) => {
    e.preventDefault();

    // TODO 実装する
  };

  onMount(() => {
    if (props.status === 404) {
      setFlash('データがない');
      window.location.href = '/song-types';
    } else if (props.status === 422) {
      setFlash('リクエストがおかしい');
      window.location.href = '/song-types';
    } else if (!props.data) {
      // TODO ちゃんとしたハンドリングをする
      alert('エラーが発生した');
    }
  });

  return (
    // TODO ローディング用のコンポーネントを用意する
    <Show when={props.data} fallback={<p>読み込み中...</p>}>
      <form onsubmit={handleSubmit}>
        <fieldset class="fieldset bg-base-200 border-base-300 rounded-box w-xs border p-4">
          <legend class="fieldset-legend">楽曲種別詳細</legend>

          <label class="label">楽曲種別名</label>
          <input type="text" class="input" value={props.data?.songType.songTypeName} />

          <label class="label">表示順</label>
          <input type="number" class="input" required min="1" value={props.data?.songType.orderNo} />

          <div class="flex justify-between gap-2">
            <button class="btn btn-error mt-4">削除</button>
            <button class="btn btn-neutral mt-4">更新</button>
          </div>
        </fieldset>
      </form>
    </Show>
  );
};
