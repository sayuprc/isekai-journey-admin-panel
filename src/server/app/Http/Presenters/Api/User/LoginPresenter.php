<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\User;

use Illuminate\Http\JsonResponse;
use ResultType\Result;
use Symfony\Component\HttpFoundation\Cookie;
use User\Application\UseCase\Login\LoginOutputData;

class LoginPresenter
{
    // 60 分
    private const int ACCESS_TOKEN_COOKIE_TTL = 60;

    // 7 日
    private const int REFRESH_TOKEN_COOKIE_TTL = 60 * 24 * 7;

    /**
     * @param Result<LoginOutputData, string> $result
     */
    public function present(Result $result): JsonResponse
    {
        $output = $result->unwrap();

        return response()
            ->json(status: 200)
            ->cookie(
                $this->createCookie(
                    'access_token',
                    $output->accessToken->jwt->value,
                    self::ACCESS_TOKEN_COOKIE_TTL
                )
            )
            ->cookie(
                $this->createCookie(
                    'refresh_token',
                    $output->refreshToken->token->value,
                    self::REFRESH_TOKEN_COOKIE_TTL
                )
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
            sameSite: 'Strict'
        );
    }
}
