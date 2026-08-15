import { Show } from 'solid-js';
import type { PlaceKindValue } from '../../generated';
import { client } from '../../utils/client';
import { createFormErrors } from '../../utils/form-error';
import { createSubmitting } from '../../utils/use-submitting';
import { setFlash } from '../Flash';
import { FormError } from '../FormError';

export const CreateForm = () => {
  const { formError, getFieldError, clearErrors, handleError } = createFormErrors();
  const { isSubmitting, withSubmitting } = createSubmitting();

  const handleSubmit = withSubmitting(async (e: Event) => {
    e.preventDefault();
    clearErrors();

    const form = e.target as HTMLFormElement;
    const formData = new FormData(form);

    const { data, error, status } = await client.api.places.post({
      name: formData.get('name')?.toString() ?? '',
      kindValue: Number(formData.get('kindValue')) as PlaceKindValue,
    });

    if (data) {
      setFlash('作成しました');
      window.location.href = '/places';
      return;
    }

    handleError(status, error);
  });

  return (
    <form onsubmit={handleSubmit}>
      <a href="/places" class="btn btn-ghost btn-sm mb-4">
        ← 一覧に戻る
      </a>
      <FormError message={formError()} onClose={clearErrors} />
      <div class="max-w-4xl space-y-6">
        <fieldset class="fieldset bg-base-200 border-base-300 rounded-box border p-6">
          <legend class="px-2 text-sm font-semibold text-base-content/70">基本情報</legend>
          <label class="label">場所名</label>
          <input
            type="text"
            class="input w-full"
            name="name"
            required
            classList={{ 'input-error': !!getFieldError('name') }}
          />
          <Show when={getFieldError('name')}>{message => <p class="mt-1 text-xs text-error">{message()}</p>}</Show>

          <label class="label">種別</label>
          <select
            class="select w-full"
            name="kindValue"
            required
            classList={{ 'select-error': !!getFieldError('kindValue') }}
          >
            <option value="1">会場</option>
            <option value="2">配信先</option>
          </select>
          <Show when={getFieldError('kindValue')}>
            {message => <p class="mt-1 text-xs text-error">{message()}</p>}
          </Show>

          <div class="mt-6 flex justify-end">
            <button class="btn btn-primary" disabled={isSubmitting()}>
              {isSubmitting() ? '作成中...' : '作成'}
            </button>
          </div>
        </fieldset>
      </div>
    </form>
  );
};
