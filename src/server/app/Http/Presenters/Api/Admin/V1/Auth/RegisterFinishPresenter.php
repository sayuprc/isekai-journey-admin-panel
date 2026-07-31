<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Auth;

use App\Http\Presenters\Api\Support\ResolvesUseCaseError;
use App\Http\Responses\ApiError;
use Auth\Application\Admin\UseCase\RegisterFinish\RegisterFinishOutputData;
use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\RegisterFinishResponse;
use ResultType\Result;
use Support\UseCase\Error\InvalidInputError;
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
            static fn (RegisterFinishOutputData $output) => [
                new RegisterFinishResponse()
                    ->setAccessToken($output->accessToken->jwt->value)
                    ->setRefreshTokenId($output->refreshTokenId)
                    ->setRefreshToken($output->plainRefreshToken),
                200,
            ],
            static function (UseCaseError $error) {
                if ($error instanceof InvalidInputError) {
                    return ApiError::validationFailed($error->errors);
                }

                return ApiError::businessRuleViolation('登録に失敗しました。入力内容を確認してください。');
            },
        );

        return response()->json($data, $status);
    }
}
