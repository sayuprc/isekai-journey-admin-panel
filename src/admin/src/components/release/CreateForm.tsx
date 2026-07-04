import { Show, createSignal } from 'solid-js';
import { client } from '../../utils/client';
import { createFormErrors } from '../../utils/form-error';
import { createSubmitting } from '../../utils/use-submitting';
import { setFlash } from '../Flash';
import { FormError } from '../FormError';
import { MediaEditor, toMediaPayload } from './MediaEditor';
import type { MediumForm } from './MediaEditor';

const getReleaseGroupId = (): string => {
  if (typeof window === 'undefined') {
    return '';
  }

  return new URLSearchParams(window.location.search).get('releaseGroupId') ?? '';
};

export const CreateForm = () => {
  const releaseGroupId = getReleaseGroupId();

  const [isDisplay, setIsDisplay] = createSignal(true);
  const [media, setMedia] = createSignal<MediumForm[]>([{ formatValue: 1, tracks: [] }]);

  const { formError, getFieldError, clearErrors, handleError } = createFormErrors();
  const { isSubmitting, withSubmitting } = createSubmitting();

  const groupUrl = () => (releaseGroupId ? `/release-groups/${releaseGroupId}` : '/release-groups');

  const handleSubmit = withSubmitting(async (e: Event) => {
    e.preventDefault();
    clearErrors();

    const form = e.target as HTMLFormElement;
    const formData = new FormData(form);

    const { data, error, status } = await client.api.releases.post({
      releaseGroupId,
      name: formData.get('name')?.toString() ?? '',
      releasedOn: formData.get('releasedOn')?.toString() ?? '',
      description: formData.get('description')?.toString() ?? '',
      isDisplay: isDisplay(),
      media: toMediaPayload(media()),
    });

    if (data) {
      setFlash('作成しました');
      window.location.href = groupUrl();
      return;
    }

    handleError(status, error);
  });

  return (
    <Show
      when={releaseGroupId !== ''}
      fallback={(
        <div class="flex flex-col items-start gap-3">
          <p class="text-error">リリースグループが指定されていません。グループ詳細から追加してください。</p>
          <a href="/release-groups" class="btn btn-outline btn-sm">
            リリース一覧へ
          </a>
        </div>
      )}
    >
      <form onSubmit={handleSubmit}>
        <a href={groupUrl()} class="btn btn-ghost btn-sm mb-4">
          ← グループ詳細に戻る
        </a>
        <FormError message={formError()} onClose={clearErrors} />
        <div class="max-w-5xl space-y-6">
          <fieldset class="fieldset bg-base-200 border-base-300 rounded-box border p-6">
            <legend class="px-2 text-sm font-semibold text-base-content/70">基本情報</legend>
            <div class="grid gap-5 md:grid-cols-2">
              <div>
                <label class="label">版名</label>
                <input
                  type="text"
                  class="input w-full"
                  name="name"
                  required
                  placeholder="通常盤 / 初回限定盤 / 配信 など"
                  classList={{ 'input-error': !!getFieldError('name') }}
                />
                <Show when={getFieldError('name')}>
                  {message => <p class="mt-1 text-xs text-error">{message()}</p>}
                </Show>
              </div>

              <div>
                <label class="label">発売日</label>
                <input
                  type="date"
                  class="input w-full"
                  name="releasedOn"
                  required
                  classList={{ 'input-error': !!getFieldError('releasedOn') }}
                />
                <Show when={getFieldError('releasedOn')}>
                  {message => <p class="mt-1 text-xs text-error">{message()}</p>}
                </Show>
              </div>

              <div class="md:col-span-2">
                <label class="label">説明</label>
                <textarea
                  class="textarea textarea-bordered min-h-32 w-full"
                  name="description"
                  classList={{ 'textarea-error': !!getFieldError('description') }}
                />
                <Show when={getFieldError('description')}>
                  {message => <p class="mt-1 text-xs text-error">{message()}</p>}
                </Show>
              </div>

              <div class="md:col-span-2">
                <label class="label">表示設定</label>
                <select
                  class="select select-bordered w-full"
                  value={String(isDisplay())}
                  onChange={e => setIsDisplay(e.currentTarget.value === 'true')}
                  classList={{ 'select-error': !!getFieldError('isDisplay') }}
                >
                  <option value="true">表示する</option>
                  <option value="false">表示しない</option>
                </select>
                <Show when={getFieldError('isDisplay')}>
                  {message => <p class="mt-1 text-xs text-error">{message()}</p>}
                </Show>
              </div>
            </div>
          </fieldset>

          <MediaEditor media={media()} onChange={setMedia} fieldError={getFieldError('media')} />

          <div class="flex justify-end">
            <button class="btn btn-primary" disabled={isSubmitting()}>
              {isSubmitting() ? '作成中...' : '作成'}
            </button>
          </div>
        </div>
      </form>
    </Show>
  );
};
