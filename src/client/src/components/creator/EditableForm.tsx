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

    const { data, response } = await client.api.creators({ creatorId: creatorId }).put({
      name: formData.get('name')?.toString() ?? '',
    });

    // TODO エラーハンドリング
    if (response.status === 400) {
      alert('400');
    } else if (response.status === 404) {
      setFlash('データがありません');
      window.location.href = `/creators`;
    } else if (response.status === 422) {
      alert('422');
    } else if (!data) {
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

    const { response } = await client.api.creators({ creatorId: creatorId }).delete();

    // TODO エラーハンドリング
    if (response.status === 400) {
      alert('400');
    } else if (response.status === 422) {
      alert('422');
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
