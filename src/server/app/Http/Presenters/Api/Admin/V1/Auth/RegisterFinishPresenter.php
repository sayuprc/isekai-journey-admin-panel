<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Auth;

use App\Http\Presenters\Api\Support\ResolvesUseCaseError;
use Auth\Application\Admin\UseCase\RegisterFinish\RegisterFinishOutputData;
use Illuminate\Http\JsonResponse;
use OpenAPI\Client\Model\ErrorResponse;
use OpenAPI\Client\Model\RegisterFinishResponse;
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
            fn (RegisterFinishOutputData $output) => [
                new RegisterFinishResponse()
                    ->setAccessToken($output->accessToken->jwt->value)
                    ->setRefreshTokenId($output->refreshTokenId)
                    ->setRefreshToken($output->plainRefreshToken),
                200,
            ],
            function (UseCaseError $error) {
                if ($error instanceof InvalidInputError) {
                    return [$this->toValidationError($error), 422];
                }

                return [new ErrorResponse()->setMessage('登録に失敗しました。入力内容を確認してください。'), 400];
            },
        );

        return response()->json($data, $status);
    }
}
