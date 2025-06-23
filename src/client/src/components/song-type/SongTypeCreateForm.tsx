import { client } from '../../utils/client';
import { setFlash } from '../Flash';

export const Form = () => {
  const handleSubmit = async (e: Event) => {
    e.preventDefault();

    const form = e.target as HTMLFormElement;
    const formData = new FormData(form);

    const { data } = await client.POST('/song-types', {
      body: {
        songTypeName: formData.get('songTypeName')?.toString() ?? '',
        orderNo: Number(formData.get('orderNo')),
      },
    });

    if (!data) {
      // TODO エラーハンドリング
      throw new Error();
    }

    setFlash('作成しました');
    window.location.href = '/song-types';
  };

  return (
    <form onsubmit={handleSubmit}>
      <fieldset class="fieldset bg-base-200 border-base-300 rounded-box w-xs border p-4">
        <legend class="fieldset-legend">楽曲種別作成</legend>

        <label class="label">楽曲種別名</label>
        <input type="text" class="input" name="songTypeName" />

        <label class="label">表示順</label>
        <input type="number" class="input" name="orderNo" required min="1" />

        <button class="btn btn-neutral mt-4">作成</button>
      </fieldset>
    </form>
  );
};
