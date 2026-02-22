import { onMount, Show } from 'solid-js';
import type { components } from '../../generated/schema';
import { client } from '../../utils/client';
import { setFlash } from '../Flash';

interface Props {
  data?: { creator: components['schemas']['Creator'] };
  status: number;
}

export const EditableForm = (props: Props) => {
  const handleSubmit = async (e: Event) => {
    e.preventDefault();
  };

  const handleUpdate = async (e: Event) => {
    e.preventDefault();

    const form = (e.target as HTMLButtonElement).form as HTMLFormElement;
    const formData = new FormData(form);

    const creatorId = props.data?.creator.creatorId;

    if (!creatorId) {
      alert('更新対象のクリエイターIDを取得できませんでした');
      return;
    }

    const { data, error, response } = await client.PUT('/creators/{creatorId}', {
      params: {
        path: {
          creatorId: creatorId,
        },
      },
      body: {
        name: formData.get('name')?.toString() ?? '',
      },
    });

    // TODO リクエストはリポジトリ経由にし、レスポンス型を別途定義する
    if (response.status === 400) {
      // TODO わかりやすい表示にする
      const errorAs = error as components['schemas']['ErrorResponse'];
      alert(`リクエストが不正 ${errorAs.message}`);
    } else if (response.status === 404) {
      setFlash('データがありません');
      window.location.href = `/creators`;
    } else if (response.status === 422) {
      // TODO わかりやすい表示にする
      const errorAs = error as components['schemas']['ValidationError'];
      alert(`エラー ${errorAs.field}: ${errorAs.message}`);
    } else if (!data) {
      // TODO エラーハンドリング
      throw new Error();
    } else {
      setFlash('更新しました');
      window.location.href = `/creators`;
    }
  };

  const handleDelete = async (e: Event) => {
    e.preventDefault();

    if (!window.confirm('削除します。よろしいですか？')) {
      return;
    }

    const creatorId = props.data?.creator.creatorId;

    if (!creatorId) {
      alert('削除対象のクリエイターIDを取得できませんでした');
      return;
    }

    const { error, response } = await client.DELETE('/creators/{creatorId}', {
      params: {
        path: {
          creatorId: creatorId,
        },
      },
    });

    // TODO リクエストはリポジトリ経由にし、レスポンス型を別途定義する
    if (response.status === 400) {
      const errorAs = error as components['schemas']['ErrorResponse'];
      alert(errorAs.message);
    } else if (response.status === 422) {
      // TODO わかりやすい表示にする
      const errorAs = error as components['schemas']['ValidationError'];
      alert(`エラー ${errorAs.field}: ${errorAs.message}`);
    } else {
      setFlash('削除しました');
      window.location.href = `/creators`;
    }
  };

  onMount(() => {
    if (props.status === 404) {
      setFlash('データがない');
      window.location.href = '/creators';
    } else if (props.status === 422) {
      setFlash('リクエストがおかしい');
      window.location.href = '/creators';
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
          <legend class="fieldset-legend">クリエイター詳細</legend>

          <label class="label">クリエイター名</label>
          <input type="text" class="input" name="name" value={props.data?.creator.name} />

          <div class="flex justify-between gap-2">
            <button onClick={handleDelete} class="btn btn-error mt-4">削除</button>
            <button onClick={handleUpdate} class="btn btn-neutral mt-4">更新</button>
          </div>
        </fieldset>
      </form>
    </Show>
  );
};
