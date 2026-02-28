import { client } from '../../utils/client';
import { setFlash } from '../Flash';

export const CreateForm = () => {
  const handleSubmit = async (e: Event) => {
    e.preventDefault();

    const form = e.target as HTMLFormElement;
    const formData = new FormData(form);

    const { data, status } = await client.api.performers.post({
      name: formData.get('name')?.toString() ?? '',
    });

    // TODO エラーハンドリング
    if (status === 400) {
      alert('400');
    } else if (status === 422) {
      alert('422');
    } else if (!data) {
      throw new Error();
    } else {
      setFlash('作成しました');
      window.location.href = '/performers';
    }
  };

  return (
    <form onsubmit={handleSubmit}>
      <a href="/performers" class="btn btn-ghost btn-sm mb-4">← 一覧に戻る</a>
      <fieldset class="fieldset bg-base-200 border-base-300 rounded-box max-w-lg border p-6">
        <label class="label">共演者名</label>
        <input type="text" class="input w-full" name="name" required />

        <div class="mt-6 flex justify-end">
          <button class="btn btn-primary">作成</button>
        </div>
      </fieldset>
    </form>
  );
};
