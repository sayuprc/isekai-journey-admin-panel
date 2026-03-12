import { onMount, Show } from 'solid-js';
import type { components } from '../../generated/types.gen';
import { client } from '../../utils/client';
import { createFormErrors } from '../../utils/form-error';
import { createSubmitting } from '../../utils/use-submitting';
import { setFlash } from '../Flash';
import { FormError } from '../FormError';

interface Props {
  data?: { performer: components['schemas']['Performer'] };
  status: number;
}

export const EditableForm = (props: Props) => {
  const { formError, setFormError, getFieldError, clearErrors, handleError } = createFormErrors();
  const { isSubmitting, withSubmitting } = createSubmitting();

  const handleSubmit = async (e: Event) => {
    e.preventDefault();
  };

  const handleUpdate = withSubmitting(async (e: Event) => {
    e.preventDefault();
    clearErrors();

    const form = (e.target as HTMLButtonElement).form as HTMLFormElement;
    const formData = new FormData(form);

    const performerId = props.data?.performer.performerId;

    if (!performerId) {
      setFormError('更新対象の共演者IDを取得できませんでした');
      return;
    }

    const { data, error, status } = await client.api.performers({ performerId: performerId }).put({
      name: formData.get('name')?.toString() ?? '',
      orderNo: Number(formData.get('orderNo')),
    });

    if (data) {
      setFlash('更新しました');
      window.location.href = `/performers`;
      return;
    }

    if (status === 404) {
      setFlash('データがありません');
      window.location.href = `/performers`;
      return;
    }

    handleError(status, error);
  });

  const handleDelete = withSubmitting(async (e: Event) => {
    e.preventDefault();

    if (!window.confirm('削除します。よろしいですか？')) {
      return;
    }

    clearErrors();

    const performerId = props.data?.performer.performerId;

    if (!performerId) {
      setFormError('削除対象の共演者IDを取得できませんでした');
      return;
    }

    const { error, status } = await client.api.performers({ performerId: performerId }).delete();

    if (error) {
      handleError(status, error);
      return;
    }

    setFlash('削除しました');
    window.location.href = `/performers`;
  });

  onMount(() => {
    if (props.status === 404) {
      setFlash('データがありません');
      window.location.href = '/performers';
    } else if (props.status === 422) {
      setFlash('不正なリクエストです');
      window.location.href = '/performers';
    } else if (!props.data) {
      setFormError('予期しないエラーが発生しました');
    }
  });

  return (
    // TODO ローディング用のコンポーネントを用意する
    <Show when={props.data} fallback={<p>読み込み中...</p>}>
      <a href="/performers" class="btn btn-ghost btn-sm mb-4">← 一覧に戻る</a>
      <FormError message={formError()} onClose={clearErrors} />
      <form onsubmit={handleSubmit}>
        <fieldset class="fieldset bg-base-200 border-base-300 rounded-box max-w-lg border p-6">
          <label class="label">共演者名</label>
          <input type="text" class="input w-full" name="name" value={props.data?.performer.name} classList={{ 'input-error': !!getFieldError('name') }} />
          <Show when={getFieldError('name')}>
            {message => <p class="mt-1 text-xs text-error">{message()}</p>}
          </Show>

          <label class="label">表示順</label>
          <input type="number" class="input w-full" name="orderNo" required min="1" value={props.data?.performer.orderNo} classList={{ 'input-error': !!getFieldError('orderNo') }} />
          <Show when={getFieldError('orderNo')}>
            {message => <p class="mt-1 text-xs text-error">{message()}</p>}
          </Show>

          <div class="mt-6 flex justify-end">
            <button onClick={handleUpdate} class="btn btn-primary" disabled={isSubmitting()}>
              {isSubmitting() ? '更新中...' : '更新'}
            </button>
          </div>
        </fieldset>
      </form>

      <div class="divider max-w-lg" />

      <div class="max-w-lg rounded-box border border-error/20 bg-error/5 p-6">
        <h3 class="font-semibold text-error">危険な操作</h3>
        <p class="mt-1 text-sm text-base-content/60">この操作は取り消せません。</p>
        <div class="mt-4">
          <button onClick={handleDelete} class="btn btn-outline btn-error btn-sm" disabled={isSubmitting()}>
            {isSubmitting() ? '削除中...' : 'この共演者を削除する'}
          </button>
        </div>
      </div>
    </Show>
  );
};
