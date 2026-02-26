import { onMount, Show } from 'solid-js';
import type { components } from '../../generated/schema';
import { client } from '../../utils/client';
import { setFlash } from '../Flash';

interface Props {
  data?: { performer: components['schemas']['Performer'] };
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

    const performerId = props.data?.performer.performerId;

    if (!performerId) {
      alert('更新対象の共演者IDを取得できませんでした');
      return;
    }

    const { data, response } = await client.api.performers({ performerId: performerId }).put({
      name: formData.get('name')?.toString() ?? '',
      orderNo: Number(formData.get('orderNo')),
    });

    // TODO リクエストはリポジトリ経由にし、レスポンス型を別途定義する
    if (response.status === 400) {
      // TODO わかりやすい表示にする
      // const errorAs = error as components['schemas']['ErrorResponse'];
      // alert(`リクエストが不正 ${errorAs.message}`);
      alert('400');
    } else if (response.status === 404) {
      setFlash('データがありません');
      window.location.href = `/performers`;
    } else if (response.status === 422) {
      // TODO わかりやすい表示にする
      // const errorAs = error as components['schemas']['ValidationError'];
      // alert(`エラー ${errorAs.field}: ${errorAs.message}`);
      alert('422');
    } else if (!data) {
      // TODO エラーハンドリング
      throw new Error();
    } else {
      setFlash('更新しました');
      window.location.href = `/performers`;
    }
  };

  const handleDelete = async (e: Event) => {
    e.preventDefault();

    if (!window.confirm('削除します。よろしいですか？')) {
      return;
    }

    const performerId = props.data?.performer.performerId;

    if (!performerId) {
      alert('削除対象の共演者IDを取得できませんでした');
      return;
    }

    const { response } = await client.api.performers({ performerId: performerId }).delete();

    // TODO リクエストはリポジトリ経由にし、レスポンス型を別途定義する
    if (response.status === 422) {
      // TODO わかりやすい表示にする
      // const errorAs = error as components['schemas']['ValidationError'];
      // alert(`エラー ${errorAs.field}: ${errorAs.message}`);
      alert('422');
    } else {
      setFlash('削除しました');
      window.location.href = `/performers`;
    }
  };

  onMount(() => {
    if (props.status === 404) {
      setFlash('データがない');
      window.location.href = '/performers';
    } else if (props.status === 422) {
      setFlash('リクエストがおかしい');
      window.location.href = '/performers';
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
          <legend class="fieldset-legend">共演者詳細</legend>

          <label class="label">共演者名</label>
          <input type="text" class="input" name="name" value={props.data?.performer.name} />

          <label class="label">表示順</label>
          <input type="number" class="input" name="orderNo" required min="1" value={props.data?.performer.orderNo} />

          <div class="flex justify-between gap-2">
            <button onClick={handleDelete} class="btn btn-error mt-4">削除</button>
            <button onClick={handleUpdate} class="btn btn-neutral mt-4">更新</button>
          </div>
        </fieldset>
      </form>
    </Show>
  );
};
