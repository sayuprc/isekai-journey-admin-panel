import { Show, onMount } from 'solid-js';
import type { Media, MediaFormatValue, MediaTypeValue } from '../../generated';
import { client } from '../../utils/client';
import { createFormErrors } from '../../utils/form-error';
import { createSubmitting } from '../../utils/use-submitting';
import { setFlash } from '../Flash';
import { FormError } from '../FormError';

const MEDIA_TYPE_OPTIONS: Array<{ value: MediaTypeValue; label: string }> = [
  { value: 1, label: '動画' },
  { value: 2, label: '記事' },
  { value: 3, label: 'SNS投稿' },
  { value: 4, label: '公式ページ' },
  { value: 99, label: 'その他' },
];

const MEDIA_FORMAT_OPTIONS: Array<{ value: MediaFormatValue; label: string }> = [
  { value: 1, label: 'MV' },
  { value: 2, label: '音源動画' },
  { value: 3, label: '配信アーカイブ' },
  { value: 4, label: 'ショート動画' },
  { value: 5, label: 'ライブ切り抜き' },
  { value: 99, label: 'その他' },
];

interface Props {
  data?: { media: Media };
  status: number;
}

export const EditableForm = (props: Props) => {
  const back = new URLSearchParams(window.location.search).get('back') ?? '';
  const listQuery = (() => {
    if (!back.startsWith('?')) return '';
    try {
      const query = new URLSearchParams(back.slice(1)).toString();
      return query ? `?${query}` : '';
    } catch {
      return '';
    }
  })();
  const listUrl = `/media${listQuery}`;

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

    const mediaId = props.data?.media.mediaId;

    if (!mediaId) {
      setFormError('更新対象のMedia IDを取得できませんでした');
      return;
    }

    const { data, error, status } = await client.api.media({ mediaId }).put({
      title: formData.get('title')?.toString() ?? '',
      url: formData.get('url')?.toString() ?? '',
      typeValue: Number(formData.get('typeValue')) as MediaTypeValue,
      formatValue: Number(formData.get('formatValue')) as MediaFormatValue,
      isDisplay: formData.get('isDisplay') === 'true',
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
            <label class="label">タイトル</label>
            <input
              type="text"
              class="input w-full"
              name="title"
              value={props.data?.media.title}
              classList={{ 'input-error': !!getFieldError('title') }}
            />
            <Show when={getFieldError('title')}>{message => <p class="mt-1 text-xs text-error">{message()}</p>}</Show>

            <label class="label">URL</label>
            <input
              type="url"
              class="input w-full"
              name="url"
              value={props.data?.media.url}
              classList={{ 'input-error': !!getFieldError('url') }}
            />
            <Show when={getFieldError('url')}>{message => <p class="mt-1 text-xs text-error">{message()}</p>}</Show>

            <div class="grid gap-4 md:grid-cols-2">
              <div>
                <label class="label">種別</label>
                <select
                  class="select w-full"
                  name="typeValue"
                  value={props.data?.media.type.value}
                  classList={{ 'select-error': !!getFieldError('typeValue') }}
                >
                  {MEDIA_TYPE_OPTIONS.map(option => <option value={option.value}>{option.label}</option>)}
                </select>
                <Show when={getFieldError('typeValue')}>{message => <p class="mt-1 text-xs text-error">{message()}</p>}</Show>
              </div>

              <div>
                <label class="label">形式</label>
                <select
                  class="select w-full"
                  name="formatValue"
                  value={props.data?.media.format.value}
                  classList={{ 'select-error': !!getFieldError('formatValue') }}
                >
                  {MEDIA_FORMAT_OPTIONS.map(option => <option value={option.value}>{option.label}</option>)}
                </select>
                <Show when={getFieldError('formatValue')}>{message => <p class="mt-1 text-xs text-error">{message()}</p>}</Show>
              </div>
            </div>

            <label class="label">表示設定</label>
            <select
              class="select w-full"
              name="isDisplay"
              value={String(props.data?.media.isDisplay)}
              classList={{ 'select-error': !!getFieldError('isDisplay') }}
            >
              <option value="true">表示する</option>
              <option value="false">表示しない</option>
            </select>
            <Show when={getFieldError('isDisplay')}>{message => <p class="mt-1 text-xs text-error">{message()}</p>}</Show>

            <div class="mt-6 flex justify-end">
              <button onClick={handleUpdate} class="btn btn-primary" disabled={isSubmitting()}>
                {isSubmitting() ? '更新中...' : '更新'}
              </button>
            </div>
          </fieldset>
        </form>
      </div>
    </Show>
  );
};
