import { Show } from 'solid-js';
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

    const { data, error, status } = await client.api['song-tags'].post({
      name: formData.get('name')?.toString() ?? '',
    });

    if (data) {
      setFlash('作成しました');
      window.location.href = '/song-tags/create';
      return;
    }

    handleError(status, error);
  });

  return (
    <form onsubmit={handleSubmit}>
      <FormError message={formError()} onClose={clearErrors} />
      <fieldset class="fieldset bg-base-200 border-base-300 rounded-box max-w-lg border p-6">
        <label class="label">楽曲タグ名</label>
        <input
          type="text"
          class="input w-full"
          name="name"
          required
          classList={{ 'input-error': !!getFieldError('name') }}
        />
        <Show when={getFieldError('name')}>{message => <p class="mt-1 text-xs text-error">{message()}</p>}</Show>

        <div class="mt-6 flex justify-end">
          <button class="btn btn-primary" disabled={isSubmitting()}>
            {isSubmitting() ? '作成中...' : '作成'}
          </button>
        </div>
      </fieldset>
    </form>
  );
};
