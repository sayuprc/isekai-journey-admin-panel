import { onMount, Show } from 'solid-js';
import type { components } from '../../generated/schema';
import { client } from '../../utils/client';
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

  const handleUpdate = async (e: Event) => {
    e.preventDefault();

    const form = (e.target as HTMLButtonElement).form as HTMLFormElement;
    const formData = new FormData(form);

    const songTypeId = props.data?.songType.songTypeId;

    if (!songTypeId) {
      alert('更新対象の楽曲種別IDを取得できませんでした');
      return;
    }

    const { data, error, response } = await client.PUT('/song-types/{songTypeId}', {
      params: {
        path: {
          songTypeId: songTypeId,
        },
      },
      body: {
        songTypeName: formData.get('songTypeName')?.toString() ?? '',
        orderNo: Number(formData.get('orderNo')),
      },
    });

    // TODO リクエストはリポジトリ経由にし、レスポンス型を別途定義する
    if (response.status === 400) {
      // TODO わかりやすい表示にする
      const errorAs = error as components['schemas']['ErrorResponse'];
      alert(`リクエストが不正 ${errorAs.message}`);
    } else if (response.status === 404) {
      setFlash('データがありません');
      window.location.href = `/song-types`;
    } else if (response.status === 422) {
      // TODO わかりやすい表示にする
      const errorAs = error as components['schemas']['ValidationError'];
      alert(`エラー ${errorAs.field}: ${errorAs.message}`);
    } else if (!data) {
      // TODO エラーハンドリング
      throw new Error();
    } else {
      setFlash('更新しました');
      window.location.href = `/song-types`;
    }
  };

  const handleDelete = async (e: Event) => {
    e.preventDefault();

    if (!window.confirm('削除します。よろしいですか？')) {
      return;
    }

    const songTypeId = props.data?.songType.songTypeId;

    if (!songTypeId) {
      alert('削除対象の楽曲種別IDを取得できませんでした');
      return;
    }

    const { error, response } = await client.DELETE('/song-types/{songTypeId}', {
      params: {
        path: {
          songTypeId: songTypeId,
        },
      },
    });

    // TODO リクエストはリポジトリ経由にし、レスポンス型を別途定義する
    if (response.status === 422) {
      // TODO わかりやすい表示にする
      const errorAs = error as components['schemas']['ValidationError'];
      alert(`エラー ${errorAs.field}: ${errorAs.message}`);
    } else {
      setFlash('削除しました');
      window.location.href = `/song-types`;
    }
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
          <input type="text" class="input" name="songTypeName" value={props.data?.songType.songTypeName} />

          <label class="label">表示順</label>
          <input type="number" class="input" name="orderNo" required min="1" value={props.data?.songType.orderNo} />

          <div class="flex justify-between gap-2">
            <button onclick={handleDelete} class="btn btn-error mt-4">削除</button>
            <button onClick={handleUpdate} class="btn btn-neutral mt-4">更新</button>
          </div>
        </fieldset>
      </form>
    </Show>
  );
};
