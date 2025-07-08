<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\User;

use Illuminate\Http\JsonResponse;
use OpenAPI\Client\Model\LoginResponse;
use ResultType\Result;
use User\Application\UseCase\Login\LoginOutputData;

class LoginPresenter
{
    /**
     * @param Result<LoginOutputData, string> $result
     */
    public function present(Result $result): JsonResponse
    {
        $data = $result->map(function (LoginOutputData $outputData) {
            $credential = $outputData->credential;

            return new LoginResponse()
                ->setAccessToken($credential->accessToken->jwt->value)
                ->setRefreshToken($credential->refreshToken->token->value);
        })->unwrap();

        return response()->json($data, 200);
    }
}
