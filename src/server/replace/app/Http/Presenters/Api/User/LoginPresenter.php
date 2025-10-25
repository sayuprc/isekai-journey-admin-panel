<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\User;

use App\Http\Responses\JsonResponse;
use DateTimeInterface;
use ResultType\Result;
use Support\Contracts\ClockInterface;
use Tempest\Http\Cookie\Cookie;
use Tempest\Http\Cookie\SameSite;
use User\Application\UseCase\Login\LoginOutputData;

readonly class LoginPresenter
{
    // 60 分
    private const int ACCESS_TOKEN_COOKIE_TTL = 60;

    // 7 日
    private const int REFRESH_TOKEN_COOKIE_TTL = 60 * 24 * 7;

    public function __construct(private ClockInterface $clock)
    {
    }

    /**
     * @param Result<LoginOutputData, string> $result
     */
    public function present(Result $result): JsonResponse
    {
        return $result->match(
            fn (LoginOutputData $output): JsonResponse => new JsonResponse(status: 200)
                ->addCookie(
                    $this->createCookie(
                        'access_token',
                        $output->accessToken->jwt->value,
                        $this->clock->now()->modify('+' . self::ACCESS_TOKEN_COOKIE_TTL . 'minutes'),
                    )
                )
                ->addCookie(
                    $this->createCookie(
                        'refresh_token',
                        $output->refreshToken->token->value,
                        $this->clock->now()->modify('+' . self::REFRESH_TOKEN_COOKIE_TTL . 'days'),
                    )
                ),
            fn (): JsonResponse => new JsonResponse(status: 401),
        );
    }

    private function createCookie(string $name, string $value, DateTimeInterface $minutes): Cookie
    {
        return new Cookie(
            key: $name,
            value: $value,
            expiresAt: $minutes->getTimestamp(),
            secure: true,
            httpOnly: true,
            sameSite: SameSite::STRICT
        );
    }
}
