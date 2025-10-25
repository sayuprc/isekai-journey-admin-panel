<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Responses\JsonResponse;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Router\HttpMiddleware;
use Tempest\Router\HttpMiddlewareCallable;
use User\Application\UseCase\Authenticate\AuthenticateInputData;
use User\Application\UseCase\Authenticate\AuthenticateUseCaseInterface;

class Authenticate implements HttpMiddleware
{
    public function __construct(private readonly AuthenticateUseCaseInterface $interactor)
    {
    }

    public function __invoke(Request $request, HttpMiddlewareCallable $next): Response
    {
        $accessToken = $request->getCookie('access_token');

        if (is_null($accessToken?->value)) {
            return new JsonResponse(status: 400);
        }

        $result = $this->interactor->handle(new AuthenticateInputData($accessToken->value));

        if ($result->isErr()) {
            return new JsonResponse(status: 401);
        }

        return $next($request);
    }
}
