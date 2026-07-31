<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Auth;

use App\Http\Responses\ApiError;
use Auth\Application\Admin\UseCase\Refresh\RefreshOutputData;
use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\RefreshTokenResponse;
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
            static fn (RefreshOutputData $output) => response()->json(
                new RefreshTokenResponse()
                    ->setAccessToken($output->accessToken->jwt->value)
                    ->setRefreshTokenId($output->refreshTokenId)
                    ->setRefreshToken($output->plainRefreshToken),
                200,
            ),
            static function (UseCaseError $_) {
                [$payload, $status] = ApiError::unauthenticated();

                return response()->json($payload, $status);
            },
        );
    }
}
