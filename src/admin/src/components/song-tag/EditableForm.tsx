import { onMount, Show } from 'solid-js';
import type { SongTag } from '../../generated';
import { client } from '../../utils/client';
import { createFormErrors } from '../../utils/form-error';
import { createSubmitting } from '../../utils/use-submitting';
import { setFlash } from '../Flash';
import { FormError } from '../FormError';

interface Props {
  data?: { tag: SongTag };
  status: number;
}

export const EditableForm = (props: Props) => {
  const back = new URLSearchParams(window.location.search).get('back') ?? '';
  const listQuery = (() => {
    if (!back.startsWith('?')) return '';
    try {
      const q = new URLSearchParams(back.slice(1)).toString();
      return q ? `?${q}` : '';
    } catch {
      return '';
    }
  })();
  const listUrl = `/song-tags${listQuery}`;

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

    const songTagId = props.data?.tag.songTagId;

    if (!songTagId) {
      setFormError('更新対象の楽曲タグIDを取得できませんでした');
      return;
    }

    const { data, error, status } = await client.api['song-tags']({ songTagId }).put({
      name: formData.get('name')?.toString() ?? '',
      orderNo: Number(formData.get('orderNo')),
    });

    if (data) {
      setFlash('更新しました');
      window.location.href = listUrl;
      return;
    }

    if (status === 404) {
      setFlash('データがありません', 'error');
      window.location.href = listUrl;
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

    const songTagId = props.data?.tag.songTagId;

    if (!songTagId) {
      setFormError('削除対象の楽曲タグIDを取得できませんでした');
      return;
    }

    const { error, status } = await client.api['song-tags']({ songTagId }).delete();

    if (error) {
      handleError(status, error);
      return;
    }

    setFlash('削除しました');
    window.location.href = listUrl;
  });

  onMount(() => {
    if (props.status === 404) {
      setFlash('データがありません', 'error');
      window.location.href = listUrl;
    } else if (props.status === 422) {
      setFlash('不正なリクエストです', 'error');
      window.location.href = listUrl;
    } else if (!props.data) {
      setFormError('予期しないエラーが発生しました');
    }
  });

  return (
    <Show when={props.data} fallback={<p>読み込み中...</p>}>
      <a href={listUrl} class="btn btn-ghost btn-sm mb-4">
        ← 一覧に戻る
      </a>
      <FormError message={formError()} onClose={clearErrors} />
      <div class="max-w-4xl space-y-6">
        <form onsubmit={handleSubmit}>
          <fieldset class="fieldset bg-base-200 border-base-300 rounded-box border p-6">
            <legend class="px-2 text-sm font-semibold text-base-content/70">基本情報</legend>
            <label class="label">楽曲タグ名</label>
            <input
              type="text"
              class="input w-full"
              name="name"
              value={props.data?.tag.name}
              classList={{ 'input-error': !!getFieldError('name') }}
            />
            <Show when={getFieldError('name')}>{message => <p class="mt-1 text-xs text-error">{message()}</p>}</Show>

            <label class="label">表示順</label>
            <input
              type="number"
              class="input w-full"
              name="orderNo"
              required
              min="1"
              value={props.data?.tag.orderNo}
              classList={{ 'input-error': !!getFieldError('orderNo') }}
            />
            <Show when={getFieldError('orderNo')}>{message => <p class="mt-1 text-xs text-error">{message()}</p>}</Show>

            <div class="mt-6 flex justify-end">
              <button onClick={handleUpdate} class="btn btn-primary" disabled={isSubmitting()}>
                {isSubmitting() ? '更新中...' : '更新'}
              </button>
            </div>
          </fieldset>
        </form>

        <fieldset class="rounded-box border border-error/20 bg-error/5 p-6">
          <legend class="px-2 text-sm font-semibold text-error">危険な操作</legend>
          <p class="mt-1 text-sm text-base-content/60">この操作は取り消せません。</p>
          <div class="mt-4">
            <button onClick={handleDelete} class="btn btn-outline btn-error btn-sm" disabled={isSubmitting()}>
              {isSubmitting() ? '削除中...' : 'この楽曲タグを削除する'}
            </button>
          </div>
        </fieldset>
      </div>
    </Show>
  );
};
