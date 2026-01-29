<?php

declare(strict_types=1);

namespace Tests\Support\Domain;

use Auth\Domain\Models\Credential\AccessToken\AccessToken;
use Auth\Domain\Models\Credential\AccessToken\Jwt;
use Auth\Domain\Models\Credential\RefreshToken\ConsumptionStatus;
use Auth\Domain\Models\Credential\RefreshToken\ExpiredAt;
use Auth\Domain\Models\Credential\RefreshToken\RefreshToken;
use Auth\Domain\Models\Credential\RefreshToken\RefreshTokenId;
use Auth\Domain\Models\Credential\RefreshToken\TokenValue;
use Creator\Domain\Models\Creator;
use Creator\Domain\Models\CreatorId;
use Creator\Domain\Models\CreatorName;
use DateTimeImmutable;
use Performer\Domain\Models\Performer;
use Performer\Domain\Models\PerformerId;
use Performer\Domain\Models\PerformerName;
use Support\Domain\ValueObjects\OrderNo;
use User\Domain\Models\Email;
use User\Domain\Models\HashedPassword;
use User\Domain\Models\User;
use User\Domain\Models\UserId;

trait EntityFactory
{
    protected function createCreator(string $creatorId, string $creatorName): Creator
    {
        return new Creator(
            CreatorId::create($creatorId)->unwrap(),
            CreatorName::create($creatorName)->unwrap(),
        );
    }

    protected function createPerformer(string $performerId, string $performerName, int $orderNo): Performer
    {
        return new Performer(
            PerformerId::create($performerId)->unwrap(),
            PerformerName::create($performerName)->unwrap(),
            OrderNo::create($orderNo)->unwrap(),
        );
    }

    protected function createUser(string $userId, string $email, string $hashedPassword): User
    {
        return new User(
            UserId::create($userId)->unwrap(),
            Email::create($email)->unwrap(),
            HashedPassword::create($hashedPassword)->unwrap(),
        );
    }

    protected function createAccessToken(string $jwt): AccessToken
    {
        return new AccessToken(Jwt::create($jwt)->unwrap());
    }

    protected function createRefreshToken(
        string $refreshTokenId,
        string $userId,
        string $tokenValue,
        DateTimeImmutable $expiredAt,
        ConsumptionStatus $status,
    ): RefreshToken {
        return new RefreshToken(
            RefreshTokenId::create($refreshTokenId)->unwrap(),
            UserId::create($userId)->unwrap(),
            TokenValue::create($tokenValue)->unwrap(),
            ExpiredAt::create($expiredAt)->unwrap(),
            $status,
        );
    }
}
