import { Show, createSignal } from 'solid-js';
import type { ReleaseDistributionTypeValue, ReleaseTypeValue } from '../../generated';
import { client } from '../../utils/client';
import { createFormErrors } from '../../utils/form-error';
import { createSubmitting } from '../../utils/use-submitting';
import { setFlash } from '../Flash';
import { FormError } from '../FormError';

const RELEASE_TYPE_OPTIONS: Array<{ value: ReleaseTypeValue; label: string }> = [
  { value: 1, label: 'シングル' },
  { value: 2, label: 'アルバム' },
  { value: 3, label: 'EP' },
  { value: 99, label: 'その他' },
];

const DISTRIBUTION_TYPE_OPTIONS: Array<{ value: ReleaseDistributionTypeValue; label: string }> = [
  { value: 1, label: '配信' },
  { value: 2, label: '物理' },
  { value: 99, label: 'その他' },
];

export const CreateForm = () => {
  const [typeValue, setTypeValue] = createSignal<ReleaseTypeValue>(1);
  const [distributionTypeValue, setDistributionTypeValue] = createSignal<ReleaseDistributionTypeValue>(1);
  const { formError, getFieldError, clearErrors, handleError } = createFormErrors();
  const { isSubmitting, withSubmitting } = createSubmitting();

  const handleSubmit = withSubmitting(async (e: Event) => {
    e.preventDefault();
    clearErrors();

    const form = e.target as HTMLFormElement;
    const formData = new FormData(form);

    const { data, error, status } = await client.api.releases.post({
      title: formData.get('title')?.toString() ?? '',
      typeValue: typeValue(),
      distributionTypeValue: distributionTypeValue(),
      releasedOn: formData.get('releasedOn')?.toString() ?? '',
      description: formData.get('description')?.toString() ?? '',
      isDisplay: formData.get('isDisplay') === 'true',
    });

    if (data) {
      setFlash('作成しました');
      window.location.href = '/releases';
      return;
    }

    handleError(status, error);
  });

  return (
    <form onsubmit={handleSubmit}>
      <a href="/releases" class="btn btn-ghost btn-sm mb-4">
        ← 一覧に戻る
      </a>
      <FormError message={formError()} onClose={clearErrors} />
      <div class="max-w-4xl space-y-6">
        <fieldset class="fieldset bg-base-200 border-base-300 rounded-box border p-6">
          <legend class="px-2 text-sm font-semibold text-base-content/70">基本情報</legend>
          <div class="grid gap-5 md:grid-cols-2">
            <div>
              <label class="label">タイトル</label>
              <input
                type="text"
                class="input w-full"
                name="title"
                required
                classList={{ 'input-error': !!getFieldError('title') }}
              />
              <Show when={getFieldError('title')}>{message => <p class="mt-1 text-xs text-error">{message()}</p>}</Show>
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
              <Show when={getFieldError('releasedOn')}>{message => <p class="mt-1 text-xs text-error">{message()}</p>}</Show>
            </div>

            <div>
              <label class="label">種別</label>
              <select
                class="select select-bordered w-full"
                name="typeValue"
                value={String(typeValue())}
                onChange={e => setTypeValue(Number(e.currentTarget.value) as ReleaseTypeValue)}
              >
                {RELEASE_TYPE_OPTIONS.map(option => (
                  <option value={option.value}>{option.label}</option>
                ))}
              </select>
              <Show when={getFieldError('typeValue')}>{message => <p class="mt-1 text-xs text-error">{message()}</p>}</Show>
            </div>

            <div>
              <label class="label">流通形態</label>
              <select
                class="select select-bordered w-full"
                name="distributionTypeValue"
                value={String(distributionTypeValue())}
                onChange={e => setDistributionTypeValue(Number(e.currentTarget.value) as ReleaseDistributionTypeValue)}
              >
                {DISTRIBUTION_TYPE_OPTIONS.map(option => (
                  <option value={option.value}>{option.label}</option>
                ))}
              </select>
              <Show when={getFieldError('distributionTypeValue')}>
                {message => <p class="mt-1 text-xs text-error">{message()}</p>}
              </Show>
            </div>

            <div class="md:col-span-2">
              <label class="label">説明</label>
              <textarea
                class="textarea textarea-bordered min-h-32 w-full"
                name="description"
                required
                classList={{ 'textarea-error': !!getFieldError('description') }}
              />
              <Show when={getFieldError('description')}>
                {message => <p class="mt-1 text-xs text-error">{message()}</p>}
              </Show>
            </div>

            <div class="md:col-span-2">
              <label class="label">表示設定</label>
              <div class="flex flex-wrap gap-4 rounded-box border border-base-300 bg-base-100 p-4">
                <label class="label cursor-pointer justify-start gap-3">
                  <input type="radio" class="radio radio-sm" name="isDisplay" value="true" checked />
                  <span class="label-text">表示する</span>
                </label>
                <label class="label cursor-pointer justify-start gap-3">
                  <input type="radio" class="radio radio-sm" name="isDisplay" value="false" />
                  <span class="label-text">表示しない</span>
                </label>
              </div>
            </div>
          </div>

          <div class="mt-6 flex justify-end">
            <button class="btn btn-primary" disabled={isSubmitting()}>
              {isSubmitting() ? '作成中...' : '作成'}
            </button>
          </div>
        </fieldset>
      </div>
    </form>
  );
};
