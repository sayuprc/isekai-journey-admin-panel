import { createResource, Match, Show, Switch } from 'solid-js';
import type { Place, PlaceKindValue } from '../../generated';
import { client } from '../../utils/client';
import { createFormErrors } from '../../utils/form-error';
import { createSubmitting } from '../../utils/use-submitting';
import { setFlash } from '../Flash';
import { FormError } from '../FormError';

interface DetailViewProps {
  placeId: string;
}

interface EditableFormProps {
  data: { place: Place };
}

interface FetchOkState {
  status: 'ok';
  data: { place: Place };
}

interface FetchErrorState {
  status: 'error';
}

type FetchState = FetchOkState | FetchErrorState;

const getListUrl = () => {
  const back = new URLSearchParams(window.location.search).get('back') ?? '';
  const listQuery = (() => {
    if (!back.startsWith('?')) return '';
    try {
      const query = new URLSearchParams(back.slice(1)).toString();
      return query ? `?${query}` : '';
    } catch {
      return '';
    }
  })();

  return `/places${listQuery}`;
};

export const DetailView = (props: DetailViewProps) => {
  const listUrl = getListUrl();

  const [resource, { refetch }] = createResource(async (): Promise<FetchState> => {
    const { data, status } = await client.api.places({ placeId: props.placeId }).get();

    if (status === 401) {
      window.location.href = '/auth/login';
      return { status: 'error' };
    }

    if (status === 404) {
      setFlash('データがありません', 'error');
      window.location.href = listUrl;
      return { status: 'error' };
    }

    if (status === 422) {
      setFlash('不正なリクエストです', 'error');
      window.location.href = listUrl;
      return { status: 'error' };
    }

    if (!data) {
      return { status: 'error' };
    }

    return { status: 'ok', data };
  });

  const loadedData = () => {
    const state = resource();
    return state?.status === 'ok' ? state.data : undefined;
  };

  return (
    <Switch>
      <Match when={resource.loading}>
        <div class="flex items-center justify-center gap-3 py-10 text-base-content/70" role="status" aria-live="polite">
          <span class="loading loading-spinner loading-md" aria-hidden="true" />
          <span>読み込み中...</span>
        </div>
      </Match>
      <Match when={resource.error || resource()?.status === 'error'}>
        <div class="flex flex-col items-start gap-3">
          <p class="text-error">データの取得に失敗しました。</p>
          <button type="button" class="btn btn-outline btn-sm" onClick={() => refetch()}>
            再試行
          </button>
        </div>
      </Match>
      <Match when={loadedData()}>{data => <EditableForm data={data()} />}</Match>
    </Switch>
  );
};

const EditableForm = (props: EditableFormProps) => {
  const listUrl = getListUrl();

  const { formError, setFormError, getFieldError, clearErrors, handleError } = createFormErrors();
  const { isSubmitting, withSubmitting } = createSubmitting();

  const handleSubmit = async (e: Event) => {
    e.preventDefault();
  };

  const handleUpdate = withSubmitting(async (e: Event) => {
    e.preventDefault();
    clearErrors();

    const form = (e.target as HTMLButtonElement).form as HTMLFormElement;
    const formData = new FormData(form);

    const placeId = props.data.place.placeId;

    if (!placeId) {
      setFormError('更新対象の場所IDを取得できませんでした');
      return;
    }

    const { data, error, status } = await client.api.places({ placeId }).put({
      name: formData.get('name')?.toString() ?? '',
      kindValue: Number(formData.get('kindValue')) as PlaceKindValue,
    });

    if (data) {
      setFlash('更新しました');
      window.location.href = listUrl;
      return;
    }

    if (status === 404) {
      setFlash('データがありません', 'error');
      window.location.href = listUrl;
      return;
    }

    handleError(status, error);
  });

  const handleDelete = withSubmitting(async (e: Event) => {
    e.preventDefault();

    if (!window.confirm('削除します。よろしいですか？')) {
      return;
    }

    clearErrors();

    const placeId = props.data.place.placeId;

    if (!placeId) {
      setFormError('削除対象の場所IDを取得できませんでした');
      return;
    }

    const { error, status } = await client.api.places({ placeId }).delete();

    if (error) {
      handleError(status, error);
      return;
    }

    setFlash('削除しました');
    window.location.href = listUrl;
  });

  return (
    <>
      <a href={listUrl} class="btn btn-ghost btn-sm mb-4">
        ← 一覧に戻る
      </a>
      <FormError message={formError()} onClose={clearErrors} />
      <div class="max-w-4xl space-y-6">
        <form onsubmit={handleSubmit}>
          <fieldset class="fieldset bg-base-200 border-base-300 rounded-box border p-6">
            <legend class="px-2 text-sm font-semibold text-base-content/70">基本情報</legend>
            <label class="label">場所名</label>
            <input
              type="text"
              class="input w-full"
              name="name"
              value={props.data.place.name}
              classList={{ 'input-error': !!getFieldError('name') }}
            />
            <Show when={getFieldError('name')}>{message => <p class="mt-1 text-xs text-error">{message()}</p>}</Show>

            <label class="label">種別</label>
            <select
              class="select w-full"
              name="kindValue"
              required
              classList={{ 'select-error': !!getFieldError('kindValue') }}
            >
              <option value="1" selected={props.data.place.kind.value === 1}>
                会場
              </option>
              <option value="2" selected={props.data.place.kind.value === 2}>
                配信先
              </option>
            </select>
            <Show when={getFieldError('kindValue')}>
              {message => <p class="mt-1 text-xs text-error">{message()}</p>}
            </Show>

            <div class="mt-6 flex justify-end">
              <button onClick={handleUpdate} class="btn btn-primary" disabled={isSubmitting()}>
                {isSubmitting() ? '更新中...' : '更新'}
              </button>
            </div>
          </fieldset>
        </form>

        <fieldset class="rounded-box border border-error/20 bg-error/5 p-6">
          <legend class="px-2 text-sm font-semibold text-error">危険な操作</legend>
          <p class="mt-1 text-sm text-base-content/60">この操作は取り消せません。</p>
          <div class="mt-4">
            <button onClick={handleDelete} class="btn btn-outline btn-error btn-sm" disabled={isSubmitting()}>
              {isSubmitting() ? '削除中...' : 'この場所を削除する'}
            </button>
          </div>
        </fieldset>
      </div>
    </>
  );
};
