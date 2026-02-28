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

    // TODO エラーハンドリング
    if (response.status === 400) {
      alert('400');
    } else if (response.status === 404) {
      setFlash('データがありません');
      window.location.href = `/performers`;
    } else if (response.status === 422) {
      alert('422');
    } else if (!data) {
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

    // TODO エラーハンドリング
    if (response.status === 422) {
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
      <a href="/performers" class="btn btn-ghost btn-sm mb-4">← 一覧に戻る</a>
      <form onsubmit={handleSubmit}>
        <fieldset class="fieldset bg-base-200 border-base-300 rounded-box max-w-lg border p-6">
          <label class="label">共演者名</label>
          <input type="text" class="input w-full" name="name" value={props.data?.performer.name} />

          <label class="label">表示順</label>
          <input type="number" class="input w-full" name="orderNo" required min="1" value={props.data?.performer.orderNo} />

          <div class="mt-6 flex justify-end">
            <button onClick={handleUpdate} class="btn btn-neutral">更新</button>
          </div>
        </fieldset>
      </form>

      <div class="divider max-w-lg" />

      <div class="max-w-lg rounded-box border border-error/20 bg-error/5 p-6">
        <h3 class="font-semibold text-error">危険な操作</h3>
        <p class="mt-1 text-sm text-base-content/60">この操作は取り消せません。</p>
        <div class="mt-4">
          <button onClick={handleDelete} class="btn btn-outline btn-error btn-sm">この共演者を削除する</button>
        </div>
      </div>
    </Show>
  );
};
