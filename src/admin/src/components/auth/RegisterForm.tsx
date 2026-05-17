import { Show } from 'solid-js';
import { client } from '../../utils/client';
import { createFormErrors } from '../../utils/form-error';
import { createSubmitting } from '../../utils/use-submitting';
import { setFlash } from '../Flash';
import { FormError } from '../FormError';

export const RegisterForm = () => {
  const { formError, setFormError, getFieldError, clearErrors, handleError } = createFormErrors();
  const { isSubmitting, withSubmitting } = createSubmitting();

  const handleSubmit = withSubmitting(async (e: Event) => {
    e.preventDefault();
    clearErrors();

    const form = e.target as HTMLFormElement;
    const formData = new FormData(form);

    const { error, status } = await client.api.auth.register.post({
      token: formData.get('token')?.toString() ?? '',
      email: formData.get('email')?.toString() ?? '',
      name: formData.get('name')?.toString() ?? '',
      password: formData.get('password')?.toString() ?? '',
    });

    if (!error) {
      setFlash('登録しました');
      window.location.href = '/song-types';
      return;
    }

    if (status === 400) {
      setFormError('登録に失敗しました。トークンを確認してください。');
      return;
    }

    handleError(status, error);
  });

  return (
    <form onsubmit={handleSubmit}>
      <FormError message={formError()} onClose={clearErrors} />
      <fieldset class="fieldset bg-base-200 border-base-300 rounded-box w-xs border p-4">
        <label class="label">登録トークン</label>
        <input
          type="text"
          class="input"
          name="token"
          required
          classList={{ 'input-error': !!getFieldError('token') }}
        />
        <Show when={getFieldError('token')}>{message => <p class="mt-1 text-xs text-error">{message()}</p>}</Show>

        <label class="label">メールアドレス</label>
        <input
          type="email"
          class="input"
          name="email"
          required
          classList={{ 'input-error': !!getFieldError('email') }}
        />
        <Show when={getFieldError('email')}>{message => <p class="mt-1 text-xs text-error">{message()}</p>}</Show>

        <label class="label">名前</label>
        <input
          type="text"
          class="input"
          name="name"
          required
          classList={{ 'input-error': !!getFieldError('name') }}
        />
        <Show when={getFieldError('name')}>{message => <p class="mt-1 text-xs text-error">{message()}</p>}</Show>

        <label class="label">パスワード</label>
        <input
          type="password"
          class="input"
          name="password"
          required
          classList={{ 'input-error': !!getFieldError('password') }}
        />
        <Show when={getFieldError('password')}>{message => <p class="mt-1 text-xs text-error">{message()}</p>}</Show>

        <button class="btn btn-primary mt-4" disabled={isSubmitting()}>
          {isSubmitting() ? '登録中...' : '登録'}
        </button>
      </fieldset>
    </form>
  );
};
