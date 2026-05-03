<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Auth;

use Auth\Application\UseCase\Refresh\RefreshOutputData;
use Illuminate\Http\JsonResponse;
use OpenAPI\Client\Model\RefreshTokenResponse;
use ResultType\Result;
use Support\UseCase\Error\UseCaseError;

class RefreshPresenter
{
    /**
     * @param Result<RefreshOutputData, UseCaseError> $result
     */
    public function present(Result $result): JsonResponse
    {
        return $result->match(
            fn (RefreshOutputData $output) => response()->json(
                new RefreshTokenResponse()
                    ->setAccessToken($output->accessToken->jwt->value)
                    ->setRefreshTokenId($output->refreshTokenId)
                    ->setRefreshToken($output->plainRefreshToken),
                200,
            ),
            fn (UseCaseError $_) => response()->json(status: 401),
        );
    }
}
