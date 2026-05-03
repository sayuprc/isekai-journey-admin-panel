<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Auth\Application\UseCase\Authenticate\AuthenticateInputData;
use Auth\Application\UseCase\Authenticate\AuthenticateUseCase;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Authenticate
{
    public function __construct(private readonly AuthenticateUseCase $useCase)
    {
    }

    /**
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $accessToken = $request->bearerToken();

        if (! is_string($accessToken)) {
            return response()->json(status: 401);
        }

        $result = $this->useCase->handle(new AuthenticateInputData($accessToken));

        if ($result->isErr()) {
            return response()->json(status: 401);
        }

        return $next($request);
    }
}
