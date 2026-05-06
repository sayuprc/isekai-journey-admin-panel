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

    const email = formData.get('email')?.toString() ?? '';
    const started = await client.api.auth.login.start.post({ email });

    if (started.error || !started.data) {
      if (started.status === 401) {
        setFormError('メールアドレスまたはパスキーが正しくありません');
        return;
      }

      handleError(started.status, started.error);
      return;
    }

    try {
      const credential = await authenticatePasskey(started.data.publicKey as Record<string, unknown>);
      const finished = await client.api.auth.login.finish.post({
        authCeremonyId: started.data.authCeremonyId,
        credential,
      });

      if (finished.error) {
        if (finished.status === 401) {
          setFormError('メールアドレスまたはパスキーが正しくありません');
          return;
        }

        handleError(finished.status, finished.error);
        return;
      }

      setFlash('ログインしました');
      window.location.href = '/song-types';
    } catch (error) {
      setFormError(error instanceof Error ? error.message : 'パスキーログインに失敗しました');
      return;
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
          {isSubmitting() ? 'ログイン中...' : 'パスキーでログイン'}
        </button>

        <a class="link link-hover mt-3 text-sm" href="/auth/register">
          登録トークンを持っている場合はこちら
        </a>
      </fieldset>
    </form>
  );
};
