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
      <fieldset class="fieldset bg-base-200 border-base-300 rounded-box w-xs border p-4">
        <legend class="fieldset-legend">クリエイター作成</legend>

        <label class="label">クリエイター名</label>
        <input type="text" class="input" name="name" />

        <button class="btn btn-neutral mt-4">作成</button>
      </fieldset>
    </form>
  );
};
