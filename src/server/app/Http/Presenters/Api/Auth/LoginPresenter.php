<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Auth;

use App\Http\Presenters\Api\Support\ResolvesUseCaseError;
use Auth\Application\UseCase\Login\LoginOutputData;
use Illuminate\Http\JsonResponse;
use OpenAPI\Client\Model\ErrorResponse;
use OpenAPI\Client\Model\LoginResponse;
use ResultType\Result;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\UseCaseError;

class LoginPresenter
{
    use ResolvesUseCaseError;

    /**
     * @param Result<LoginOutputData, UseCaseError> $result
     */
    public function present(Result $result): JsonResponse
    {
        [$data, $status] = $result->match(
            fn (LoginOutputData $output) => [
                new LoginResponse()
                    ->setAccessToken($output->accessToken->jwt->value)
                    ->setRefreshToken($output->refreshToken->token->value),
                200,
            ],
            function (UseCaseError $error) {
                if ($error instanceof InvalidInputError) {
                    return [$this->toValidationError($error), 422];
                }

                return [
                    new ErrorResponse()->setMessage('認証に失敗しました'),
                    400,
                ];
            },
        );

        return response()->json($data, $status);
    }
}
