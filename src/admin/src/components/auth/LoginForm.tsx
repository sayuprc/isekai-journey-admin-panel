import { Show } from 'solid-js';
import { client } from '../../utils/client';
import { createFormErrors } from '../../utils/form-error';
import { createSubmitting } from '../../utils/use-submitting';
import { authenticatePasskey } from '../../utils/webauthn';
import { setFlash } from '../Flash';
import { FormError } from '../FormError';

export const LoginForm = () => {
  const { formError, setFormError, getFieldError, clearErrors, handleError } = createFormErrors();
  const { isSubmitting, withSubmitting } = createSubmitting();

  const handleSubmit = withSubmitting(async (e: Event) => {
    e.preventDefault();
    clearErrors();

    const form = e.target as HTMLFormElement;
    const formData = new FormData(form);

    const start = await client.api.auth.login.start.post({
      email: formData.get('email')?.toString() ?? '',
    });

    if (start.error) {
      if (start.status === 400 || start.status === 401) {
        setFormError('パスキー認証に失敗しました');
        return;
      }

      handleError(start.status, start.error);
      return;
    }

    try {
      const credential = await authenticatePasskey(start.data.publicKey as Record<string, unknown>);
      const finish = await client.api.auth.login.finish.post({
        authCeremonyId: start.data.authCeremonyId,
        credential,
      });

      if (finish.error) {
        if (finish.status === 400 || finish.status === 401) {
          setFormError('パスキー認証に失敗しました');
          return;
        }

        handleError(finish.status, finish.error);
        return;
      }

      setFlash('ログインしました');
      window.location.href = '/song-types';
    } catch {
      setFormError('パスキー認証に失敗しました');
    }
  });

  return (
    <form onsubmit={handleSubmit}>
      <FormError message={formError()} onClose={clearErrors} />
      <fieldset class="fieldset bg-base-200 border-base-300 rounded-box w-xs border p-4">
        <label class="label">メールアドレス</label>
        <input
          type="email"
          class="input"
          name="email"
          required
          classList={{ 'input-error': !!getFieldError('email') }}
        />
        <Show when={getFieldError('email')}>{message => <p class="mt-1 text-xs text-error">{message()}</p>}</Show>

        <button class="btn btn-primary mt-4" disabled={isSubmitting()}>
          {isSubmitting() ? 'ログイン中...' : 'ログイン'}
        </button>
      </fieldset>
    </form>
  );
};
