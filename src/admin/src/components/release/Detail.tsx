import { For, Show, onMount } from 'solid-js';
import type { Release, ReleaseDistributionTypeValue, ReleaseTypeValue } from '../../generated';
import { setFlash } from '../Flash';

const getReleaseTypeLabel = (value: ReleaseTypeValue): string => {
  switch (value) {
    case 1:
      return 'シングル';
    case 2:
      return 'アルバム';
    case 3:
      return 'EP';
    case 99:
      return 'その他';
  }
};

const getDistributionTypeLabel = (value: ReleaseDistributionTypeValue): string => {
  switch (value) {
    case 1:
      return '配信';
    case 2:
      return '物理';
    case 99:
      return 'その他';
  }
};

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

interface Props {
  data?: { release: Release };
  status: number;
}

export const Detail = (props: Props) => {
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
  const listUrl = `/releases${listQuery}`;

  onMount(() => {
    if (props.status === 404) {
      setFlash('データがありません', 'error');
      window.location.href = listUrl;
    } else if (props.status === 422) {
      setFlash('不正なリクエストです', 'error');
      window.location.href = listUrl;
    } else if (!props.data) {
      setFlash('予期しないエラーが発生しました', 'error');
      window.location.href = listUrl;
    }
  });

  return (
    <Show when={props.data} fallback={<p>読み込み中...</p>}>
      <a href={listUrl} class="btn btn-ghost btn-sm mb-4">
        ← 一覧に戻る
      </a>

      <div class="max-w-4xl space-y-6">
        <fieldset class="fieldset bg-base-200 border-base-300 rounded-box border p-6">
          <legend class="px-2 text-sm font-semibold text-base-content/70">基本情報</legend>

          <div class="grid gap-4 md:grid-cols-2">
            <div>
              <div class="text-sm text-base-content/60">タイトル</div>
              <div class="mt-1 font-medium">{props.data?.release.title}</div>
            </div>
            <div>
              <div class="text-sm text-base-content/60">発売日</div>
              <div class="mt-1">{normalizeDateDisplayValue(props.data?.release.releasedOn)}</div>
            </div>
            <div>
              <div class="text-sm text-base-content/60">種別</div>
              <div class="mt-1">{getReleaseTypeLabel(props.data!.release.typeValue)}</div>
            </div>
            <div>
              <div class="text-sm text-base-content/60">流通形態</div>
              <div class="mt-1">{getDistributionTypeLabel(props.data!.release.distributionTypeValue)}</div>
            </div>
            <div>
              <div class="text-sm text-base-content/60">表示設定</div>
              <div class="mt-1">
                <span class={`badge badge-sm ${props.data!.release.isDisplay ? 'badge-success badge-soft' : 'badge-ghost'}`}>
                  {props.data!.release.isDisplay ? '表示する' : '表示しない'}
                </span>
              </div>
            </div>
          </div>

          <div class="mt-4">
            <div class="text-sm text-base-content/60">説明</div>
            <p class="mt-1 whitespace-pre-wrap">{props.data?.release.description || '説明はありません。'}</p>
          </div>
        </fieldset>

        <fieldset class="fieldset bg-base-200 border-base-300 rounded-box border p-6">
          <legend class="px-2 text-sm font-semibold text-base-content/70">収録曲</legend>
          <Show
            when={(props.data?.release.trackEntries.length ?? 0) > 0}
            fallback={<p class="text-sm text-base-content/60">収録曲はありません。</p>}
          >
            <div class="overflow-x-auto">
              <table class="table table-sm">
                <thead>
                  <tr>
                    <th>曲順</th>
                    <th>songId</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  <For each={props.data?.release.trackEntries}>
                    {trackEntry => (
                      <tr>
                        <td>{trackEntry.trackNo}</td>
                        <td class="font-mono text-xs">{trackEntry.songId}</td>
                        <td class="text-right">
                          <a href={`/songs/${trackEntry.songId}`} class="btn btn-ghost btn-xs">
                            楽曲を見る
                          </a>
                        </td>
                      </tr>
                    )}
                  </For>
                </tbody>
              </table>
            </div>
          </Show>
        </fieldset>
      </div>
    </Show>
  );
};
