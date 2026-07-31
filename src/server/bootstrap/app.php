<?php

declare(strict_types=1);

use App\Http\Responses\ApiError;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: [__DIR__ . '/../routes/admin.php', __DIR__ . '/../routes/viewer.php'],
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        apiPrefix: '',
    )
    ->withMiddleware(static function (Middleware $middleware) {
        $middleware->remove(ConvertEmptyStringsToNull::class);
    })
    ->withExceptions(static function (Exceptions $exceptions) {
        // API のみのアプリのため、Accept ヘッダに依存せず常に JSON で例外を返す
        $exceptions->shouldRenderJsonWhen(static fn (): bool => true);

        // 予期しない例外は統一エンベロープの 500 で返す
        // ルーティング由来の HTTP 例外 (404, 405 等) は契約外のため Laravel の既定に任せる
        $exceptions->render(static function (\Throwable $e) {
            if ($e instanceof HttpExceptionInterface) {
                return null;
            }

            [$payload, $status] = ApiError::internalError();

            return response()->json($payload, $status);
        });
    })->create();
