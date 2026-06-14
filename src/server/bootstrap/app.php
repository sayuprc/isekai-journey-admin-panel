<?php

declare(strict_types=1);

use App\Http\Middleware\AssignCloudTrace;
use App\Logging\CloudTrace;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: [__DIR__ . '/../routes/admin.php', __DIR__ . '/../routes/viewer.php'],
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        apiPrefix: '',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->remove(ConvertEmptyStringsToNull::class);
        // 全 API リクエストでログにトレースを相関させる
        $middleware->prependToGroup('api', AssignCloudTrace::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // API 専用なので例外は常に JSON で応答する (HTML エラーページを返さない)。
        $exceptions->shouldRenderJsonWhen(static fn (): bool => true);

        // 想定外の例外 (500) は内部情報を漏らさないよう generic な応答にし、調査用の
        // traceId だけを載せる。例外の class / message / file / line は Cloud Logging の
        // 同一 trace のログ側に出るので、traceId から辿れる。
        // HTTP 例外・バリデーション・認証は意味のある応答を持つので既定に委ねる。
        $exceptions->render(function (\Throwable $e, Request $request): ?JsonResponse {
            if ($e instanceof HttpExceptionInterface
                || $e instanceof ValidationException
                || $e instanceof AuthenticationException) {
                return null;
            }

            $payload = ['message' => 'Server Error'];

            $parsed = CloudTrace::parse($request);

            if (! is_null($parsed)) {
                $payload['traceId'] = $parsed['traceId'];
            }

            return response()->json($payload, Response::HTTP_INTERNAL_SERVER_ERROR);
        });
    })->create();
