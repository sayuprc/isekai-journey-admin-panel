<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Auth;

use App\Http\Presenters\Api\Support\ResolvesUseCaseError;
use Auth\Application\UseCase\Login\LoginOutputData;
use Illuminate\Http\JsonResponse;
use OpenAPI\Client\Model\PasskeyLoginFinishResponse;
use ResultType\Result;
use Support\UseCase\Error\UseCaseError;

class LoginFinishPresenter
{
    use ResolvesUseCaseError;

    /**
     * @param Result<LoginOutputData, UseCaseError> $result
     */
    public function present(Result $result): JsonResponse
    {
        [$data, $status] = $result->match(
            fn (LoginOutputData $output) => [
                new PasskeyLoginFinishResponse()
                    ->setAccessToken($output->accessToken->jwt->value)
                    ->setRefreshTokenId($output->refreshTokenId)
                    ->setRefreshToken($output->plainRefreshToken),
                200,
            ],
            fn (UseCaseError $error) => $this->resolveError($error),
        );

        return response()->json($data, $status);
    }
}
