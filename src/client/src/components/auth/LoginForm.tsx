// import type { components } from '../../generated/schema';
import { client } from '../../utils/client';
import { setFlash } from '../Flash';

export const LoginForm = () => {
  const handleSubmit = async (e: Event) => {
    e.preventDefault();

    const form = e.target as HTMLFormElement;
    const formData = new FormData(form);

    const { error, status } = await client.api.auth.login.post({
      email: formData.get('email')?.toString() ?? '',
      password: formData.get('password')?.toString() ?? '',
    });

    // TODO リクエストはリポジトリ経由にし、レスポンス型を別途定義する
    if (status === 400) {
      // TODO わかりやすい表示にする
      // const errorAs = error as components['schemas']['ErrorResponse'];
      // alert(`リクエストが不正 ${errorAs.message}`);
      alert('400 エラー');
    } else if (status === 401) {
      // TODO わかりやすい表示にする
      // alert(`認証失敗`);
      alert('401 エラー');
    } else if (status === 422) {
      // TODO わかりやすい表示にする
      // const errorAs = error as components['schemas']['ValidationError'];
      // alert(`エラー ${errorAs.field}: ${errorAs.message}`);
      alert('422 エラー');
    } else if (error) {
      // TODO エラーハンドリング
      throw new Error();
    } else {
      setFlash('ログインしました');
      window.location.href = '/song-types';
    }
  };

  return (
    <form onsubmit={handleSubmit}>
      <fieldset class="fieldset bg-base-200 border-base-300 rounded-box w-xs border p-4">
        <legend class="fieldset-legend">ログイン</legend>

        <label class="label">メールアドレス</label>
        <input type="email" class="input" name="email" required />

        <label class="label">パスワード</label>
        <input type="password" class="input" name="password" required />

        <button class="btn btn-neutral mt-4">ログイン</button>
      </fieldset>
    </form>
  );
};
