<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Auth;

use App\Http\Presenters\Api\Support\ResolvesUseCaseError;
use Auth\Application\Admin\UseCase\Recovery\RecoveryFinishOutputData;
use Illuminate\Http\JsonResponse;
use OpenAPI\Client\Model\RecoveryFinishResponse;
use ResultType\Result;
use Support\UseCase\Error\UseCaseError;

class RecoveryFinishPresenter
{
    use ResolvesUseCaseError;

    /**
     * @param Result<RecoveryFinishOutputData, UseCaseError> $result
     */
    public function present(Result $result): JsonResponse
    {
        [$data, $status] = $result->match(
            fn (RecoveryFinishOutputData $output) => [
                new RecoveryFinishResponse()
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
