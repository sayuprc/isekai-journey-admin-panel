import { client } from '../../utils/client';
import { setFlash } from '../Flash';

export const CreateForm = () => {
  const handleSubmit = async (e: Event) => {
    e.preventDefault();

    const form = e.target as HTMLFormElement;
    const formData = new FormData(form);

    const { data, response } = await client.api.creators.post({
      name: formData.get('name')?.toString() ?? '',
    });

    // TODO エラーハンドリング
    if (response.status === 400) {
      alert('400');
    } else if (response.status === 422) {
      alert('422');
    } else if (!data) {
      throw new Error();
    } else {
      setFlash('作成しました');
      window.location.href = '/creators';
    }
  };

  return (
    <form onsubmit={handleSubmit}>
      <a href="/creators" class="btn btn-ghost btn-sm mb-4">← 一覧に戻る</a>
      <fieldset class="fieldset bg-base-200 border-base-300 rounded-box max-w-lg border p-6">
        <label class="label">クリエイター名</label>
        <input type="text" class="input w-full" name="name" required />

        <div class="mt-6 flex flex justify-end">
          <button class="btn btn-neutral">作成</button>
        </div>
      </fieldset>
    </form>
  );
};
