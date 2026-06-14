<?php

declare(strict_types=1);

namespace App\Logging;

use Illuminate\Http\Request;

/**
 * Cloud Run が付与する `X-Cloud-Trace-Context` を解釈するヘルパ。
 *
 * ログ相関 ({@see AssignCloudTrace}) と 500 応答の調査用 traceId 付与で共有する。
 *
 * @see https://cloud.google.com/trace/docs/setup#force-trace
 */
final class CloudTrace
{
    /**
     * 形式: `TRACE_ID/SPAN_ID;o=TRACE_TRUE`
     *
     * @return array{traceId: string, spanId: string|null}|null
     */
    public static function parse(Request $request): ?array
    {
        $header = $request->header('X-Cloud-Trace-Context');

        if (! is_string($header) || $header === '') {
            return null;
        }

        [$traceId, $rest] = array_pad(explode('/', $header, 2), 2, null);

        if (! is_string($traceId) || $traceId === '') {
            return null;
        }

        $spanId = null;

        if (is_string($rest) && $rest !== '') {
            $span = explode(';', $rest, 2)[0];
            $spanId = $span !== '' ? $span : null;
        }

        return ['traceId' => $traceId, 'spanId' => $spanId];
    }
}
