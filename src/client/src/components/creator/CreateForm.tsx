import type { components } from '../../generated/schema';
import { client } from '../../utils/client';
import { setFlash } from '../Flash';

export const CreateForm = () => {
  const handleSubmit = async (e: Event) => {
    e.preventDefault();

    const form = e.target as HTMLFormElement;
    const formData = new FormData(form);

    const { data, error, response } = await client.POST('/creators', {
      body: {
        creatorName: formData.get('creatorName')?.toString() ?? '',
      },
    });

    // TODO リクエストはリポジトリ経由にし、レスポンス型を別途定義する
    if (response.status === 400) {
      // TODO わかりやすい表示にする
      const errorAs = error as components['schemas']['ErrorResponse'];
      alert(`リクエストが不正 ${errorAs.message}`);
    } else if (response.status === 422) {
      // TODO わかりやすい表示にする
      const errorAs = error as components['schemas']['ValidationError'];
      alert(`エラー ${errorAs.field}: ${errorAs.message}`);
    } else if (!data) {
      // TODO エラーハンドリング
      throw new Error();
    } else {
      setFlash('作成しました');
      window.location.href = '/creators';
    }
  };

  return (
    <form onsubmit={handleSubmit}>
      <fieldset class="fieldset bg-base-200 border-base-300 rounded-box w-xs border p-4">
        <legend class="fieldset-legend">クリエイター作成</legend>

        <label class="label">クリエイター名</label>
        <input type="text" class="input" name="creatorName" />

        <button class="btn btn-neutral mt-4">作成</button>
      </fieldset>
    </form>
  );
};
