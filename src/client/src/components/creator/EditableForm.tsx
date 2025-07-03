import { onMount, Show } from 'solid-js';
import type { components } from '../../generated/schema';
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

    // TODO 実装する
  };

  const handleDelete = async (e: Event) => {
    e.preventDefault();

    // TODO 実装する
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
          <input type="text" class="input" name="creatorName" value={props.data?.creator.creatorName} />

          <div class="flex justify-between gap-2">
            <button onClick={handleDelete} class="btn btn-error mt-4">削除</button>
            <button onClick={handleUpdate} class="btn btn-neutral mt-4">更新</button>
          </div>
        </fieldset>
      </form>
    </Show>
  );
};
