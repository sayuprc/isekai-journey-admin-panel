<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Auth;

use App\Http\Presenters\Api\Support\ResolvesUseCaseError;
use Auth\Application\UseCase\RegisterFinish\RegisterFinishOutputData;
use Illuminate\Http\JsonResponse;
use OpenAPI\Client\Model\PasskeyRegistrationFinishResponse;
use ResultType\Result;
use Support\UseCase\Error\UseCaseError;

class RegisterFinishPresenter
{
    use ResolvesUseCaseError;

    /**
     * @param Result<RegisterFinishOutputData, UseCaseError> $result
     */
    public function present(Result $result): JsonResponse
    {
        [$data, $status] = $result->match(
            fn (RegisterFinishOutputData $output) => [
                new PasskeyRegistrationFinishResponse()
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
