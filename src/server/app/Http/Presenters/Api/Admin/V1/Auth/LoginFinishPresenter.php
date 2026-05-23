<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Auth;

use App\Http\Presenters\Api\Support\ResolvesUseCaseError;
use Auth\Application\Admin\UseCase\Login\LoginFinishOutputData;
use Illuminate\Http\JsonResponse;
use OpenAPI\Client\Model\LoginFinishResponse;
use ResultType\Result;
use Support\UseCase\Error\UseCaseError;

class LoginFinishPresenter
{
    use ResolvesUseCaseError;

    /**
     * @param Result<LoginFinishOutputData, UseCaseError> $result
     */
    public function present(Result $result): JsonResponse
    {
        [$data, $status] = $result->match(
            fn (LoginFinishOutputData $output) => [
                new LoginFinishResponse()
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
