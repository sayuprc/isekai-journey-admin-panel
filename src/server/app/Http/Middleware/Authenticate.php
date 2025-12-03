<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Auth\Application\UseCase\Authenticate\AuthenticateInputData;
use Auth\Application\UseCase\Authenticate\AuthenticateUseCaseInterface;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Authenticate
{
    public function __construct(private readonly AuthenticateUseCaseInterface $interactor)
    {
    }

    /**
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $accessToken = $request->cookie('access_token');

        if (! is_string($accessToken)) {
            return response()->json(status: 400);
        }

        $result = $this->interactor->handle(new AuthenticateInputData($accessToken));

        if ($result->isErr()) {
            return response()->json(status: 401);
        }

        return $next($request);
    }
}
