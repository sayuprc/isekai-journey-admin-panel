<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Logging\CloudLoggingFormatter;
use App\Logging\CloudTrace;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cloud Run が付与する `X-Cloud-Trace-Context` を解釈し、ログをリクエスト単位で
 * 相関させるための trace / spanId を共有ログコンテキストへ積む。
 *
 * 値は {@see CloudLoggingFormatter} がペイロード直下へ昇格させる。
 *
 * @see https://cloud.google.com/trace/docs/setup#force-trace
 */
final class AssignCloudTrace
{
    /**
     * @param Closure(Request): Response $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $projectId = config('services.google_cloud.project_id');
        $parsed = CloudTrace::parse($request);

        $trace = null;
        $spanId = null;

        if (! is_null($parsed) && is_string($projectId) && $projectId !== '') {
            $trace = "projects/{$projectId}/traces/{$parsed['traceId']}";
            $spanId = $parsed['spanId'];
        }

        // 値が無いリクエストでもキーを上書きし、ワーカー再利用時の値残りを防ぐ
        Log::shareContext([
            'logging.googleapis.com/trace' => $trace,
            'logging.googleapis.com/spanId' => $spanId,
        ]);

        return $next($request);
    }
}
