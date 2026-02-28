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
      <fieldset class="fieldset bg-base-200 border-base-300 rounded-box w-xs border p-4">
        <legend class="fieldset-legend">共演者作成</legend>

        <label class="label">共演者名</label>
        <input type="text" class="input" name="name" required />

        <button class="btn btn-neutral mt-4">作成</button>
      </fieldset>
    </form>
  );
};
