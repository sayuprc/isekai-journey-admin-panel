import { Show } from 'solid-js';
import { client } from '../../utils/client';
import { createFormErrors } from '../../utils/form-error';
import { createSubmitting } from '../../utils/use-submitting';
import { registerPasskey } from '../../utils/webauthn';
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

    const email = formData.get('email')?.toString() ?? '';
    const registrationToken = formData.get('registrationToken')?.toString() ?? '';

    const started = await client.api.auth.register.start.post({
      email,
      registrationToken,
    });

    if (started.error || !started.data) {
      if (started.status === 401) {
        setFormError('登録トークンが無効か、期限切れです');
        return;
      }

      handleError(started.status, started.error);
      return;
    }

    try {
      const credential = await registerPasskey(started.data.publicKey as Record<string, unknown>);
      const finished = await client.api.auth.register.finish.post({
        authCeremonyId: started.data.authCeremonyId,
        credential,
      });

      if (finished.error) {
        if (finished.status === 401) {
          setFormError('登録処理に失敗しました。もう一度やり直してください');
          return;
        }

        handleError(finished.status, finished.error);
        return;
      }

      setFlash('登録が完了しました');
      window.location.href = '/song-types';
    } catch (error) {
      setFormError(error instanceof Error ? error.message : 'パスキー登録に失敗しました');
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

        <label class="label">登録トークン</label>
        <input
          type="text"
          class="input"
          name="registrationToken"
          required
          autocomplete="one-time-code"
          classList={{ 'input-error': !!getFieldError('registrationToken') }}
        />
        <Show when={getFieldError('registrationToken')}>
          {message => <p class="mt-1 text-xs text-error">{message()}</p>}
        </Show>

        <button class="btn btn-primary mt-4" disabled={isSubmitting()}>
          {isSubmitting() ? '登録中...' : 'パスキーを登録'}
        </button>

        <a class="link link-hover mt-3 text-sm" href="/auth/login">
          すでに登録済みの場合はこちら
        </a>
      </fieldset>
    </form>
  );
};
