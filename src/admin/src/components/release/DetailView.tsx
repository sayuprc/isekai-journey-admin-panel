import { Match, Show, Switch } from 'solid-js';
import type { ReleaseGetResponse } from '../../generated';

const RELEASE_TYPE_LABEL: Record<number, string> = {
  1: 'シングル',
  2: 'アルバム',
  3: 'EP',
  99: 'その他',
};

const DISTRIBUTION_TYPE_LABEL: Record<number, string> = {
  1: '配信',
  2: '物理',
  99: 'その他',
};

interface Props {
  data?: ReleaseGetResponse;
  status: number;
}

const normalizeDateDisplayValue = (value: unknown): string => {
  if (value instanceof Date) {
    return Number.isNaN(value.getTime()) ? '' : value.toISOString().slice(0, 10);
  }

  if (typeof value !== 'string') {
    return '';
  }

  if (/^\d{4}-\d{2}-\d{2}$/.test(value)) {
    return value;
  }

  const parsed = new Date(value);

  return Number.isNaN(parsed.getTime()) ? '' : parsed.toISOString().slice(0, 10);
};

export const DetailView = (props: Props) => {
  const listUrl = () => {
    if (typeof window === 'undefined') return '/releases';
    const back = new URLSearchParams(window.location.search).get('back') ?? '';
    if (!back.startsWith('?')) return '/releases';

    try {
      const query = new URLSearchParams(back.slice(1)).toString();
      return query ? `/releases?${query}` : '/releases';
    } catch {
      return '/releases';
    }
  };

  return (
    <div class="flex flex-col gap-4">
      <div>
        <a href={listUrl()} class="btn btn-ghost btn-sm">
          ← 一覧に戻る
        </a>
      </div>

      <Switch>
        <Match when={props.status === 404}>
          <div class="alert alert-warning">
            <span>リリースが見つかりません。</span>
          </div>
        </Match>
        <Match when={props.status === 422}>
          <div class="alert alert-error">
            <span>不正なリクエストです。</span>
          </div>
        </Match>
        <Match when={props.status !== 200 || !props.data}>
          <div class="alert alert-error">
            <span>データの取得に失敗しました。</span>
          </div>
        </Match>
        <Match when={props.data}>
          {result => (
            <>
              <div class="rounded-box border border-base-300 bg-base-100 p-6">
                <h2 class="mb-4 text-lg font-semibold">基本情報</h2>
                <dl class="grid grid-cols-1 gap-x-4 gap-y-3 sm:grid-cols-[10rem_1fr]">
                  <dt class="font-semibold">タイトル</dt>
                  <dd>{result().release.title}</dd>
                  <dt class="font-semibold">種別</dt>
                  <dd>{RELEASE_TYPE_LABEL[result().release.typeValue] ?? '不明'}</dd>
                  <dt class="font-semibold">流通形態</dt>
                  <dd>{DISTRIBUTION_TYPE_LABEL[result().release.distributionTypeValue] ?? '不明'}</dd>
                  <dt class="font-semibold">発売日</dt>
                  <dd>{normalizeDateDisplayValue(result().release.releasedOn)}</dd>
                  <dt class="font-semibold">表示設定</dt>
                  <dd>
                    <span class={`badge badge-sm ${result().release.isDisplay ? 'badge-success badge-soft' : 'badge-ghost'}`}>
                      {result().release.isDisplay ? '表示する' : '表示しない'}
                    </span>
                  </dd>
                  <dt class="font-semibold">説明</dt>
                  <dd class="whitespace-pre-wrap break-words">{result().release.description}</dd>
                </dl>
              </div>

              <div class="rounded-box border border-base-300 bg-base-100 p-6">
                <h2 class="mb-4 text-lg font-semibold">収録楽曲</h2>
                <Show
                  when={result().songs.length > 0}
                  fallback={<p class="text-sm text-base-content/60">収録楽曲はまだ登録されていません。</p>}
                >
                  <div class="overflow-x-auto">
                    <table class="table table-sm">
                      <thead>
                        <tr>
                          <th>曲順</th>
                          <th>楽曲名</th>
                          <th></th>
                        </tr>
                      </thead>
                      <tbody>
                        {result().songs.map(song => (
                          <tr>
                            <td>{song.trackNo}</td>
                            <td>{song.title}</td>
                            <td class="text-right">
                              <a href={`/songs/${song.songId}`} class="btn btn-ghost btn-xs">
                                楽曲を見る
                              </a>
                            </td>
                          </tr>
                        ))}
                      </tbody>
                    </table>
                  </div>
                </Show>
              </div>
            </>
          )}
        </Match>
      </Switch>
    </div>
  );
};
