<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Auth;

use Auth\Application\UseCase\Login\LoginOutputData;
use Illuminate\Http\JsonResponse;
use OpenAPI\Client\Model\ErrorResponse;
use ResultType\Result;
use Support\UseCase\Error\UseCaseError;
use Symfony\Component\HttpFoundation\Cookie;

class LoginPresenter
{
    // 60 分
    private const int ACCESS_TOKEN_COOKIE_TTL = 60;

    // 7 日
    private const int REFRESH_TOKEN_COOKIE_TTL = 60 * 24 * 7;

    /**
     * @param Result<LoginOutputData, UseCaseError> $result
     */
    public function present(Result $result): JsonResponse
    {
        return $result->match(
            fn (LoginOutputData $output) => response()
                ->json(status: 200)
                ->cookie(
                    $this->createCookie(
                        'access_token',
                        $output->accessToken->jwt->value,
                        self::ACCESS_TOKEN_COOKIE_TTL,
                    ),
                )
                ->cookie(
                    $this->createCookie(
                        'refresh_token',
                        $output->refreshToken->token->value,
                        self::REFRESH_TOKEN_COOKIE_TTL,
                    ),
                ),
            fn () => response()->json(
                new ErrorResponse()->setMessage('認証に失敗しました'),
                400,
            ),
        );
    }

    /**
     * @param non-empty-string $name
     */
    private function createCookie(string $name, string $value, int $minutes): Cookie
    {
        return cookie(
            name: $name,
            value: $value,
            minutes: $minutes,
            path: '/',
            domain: null,
            secure: true,
            httpOnly: true,
            raw: false,
            sameSite: 'Strict',
        );
    }
}
