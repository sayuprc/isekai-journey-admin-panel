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
            CreatorId::reconstruct($creatorId),
            CreatorName::reconstruct($creatorName),
        );
    }

    protected function createPerformer(string $performerId, string $performerName, int $orderNo): Performer
    {
        return new Performer(
            PerformerId::reconstruct($performerId),
            PerformerName::reconstruct($performerName),
            OrderNo::reconstruct($orderNo),
        );
    }

    protected function createUser(string $userId, string $email, string $hashedPassword): User
    {
        return new User(
            UserId::reconstruct($userId),
            Email::reconstruct($email),
            HashedPassword::reconstruct($hashedPassword),
        );
    }

    protected function createAccessToken(string $jwt): AccessToken
    {
        return new AccessToken(Jwt::reconstruct($jwt));
    }

    protected function createRefreshToken(
        string $refreshTokenId,
        string $userId,
        string $tokenValue,
        DateTimeImmutable $expiredAt,
        ConsumptionStatus $status,
    ): RefreshToken {
        return new RefreshToken(
            RefreshTokenId::reconstruct($refreshTokenId),
            UserId::reconstruct($userId),
            TokenValue::reconstruct($tokenValue),
            ExpiredAt::reconstruct($expiredAt),
            $status,
        );
    }
}
