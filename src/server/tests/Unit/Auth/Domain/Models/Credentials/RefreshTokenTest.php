<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Domain\Models\Credential;

use Auth\Domain\Models\Credential\RefreshToken\ConsumptionStatus;
use DateTimeImmutable;
use DateTimeInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class RefreshTokenTest extends TestCase
{
    use EntityFactory;

    #[Test]
    #[DataProvider('isAvailableDataProvider')]
    public function isAvailable(
        DateTimeImmutable $expiredAt,
        ConsumptionStatus $status,
        DateTimeInterface $now,
        bool $expected,
    ): void {
        $refreshToken = $this->createRefreshToken(
            $this->generateUuid(),
            $this->generateUuid(),
            'token',
            $expiredAt,
            $status,
        );

        $this->assertSame($expected, $refreshToken->isAvailable($now));
    }

    public static function isAvailableDataProvider(): array
    {
        return [
            [
                new DateTimeImmutable('2019-12-09 12:00:00'),
                ConsumptionStatus::Unused,
                new DateTimeImmutable('2019-12-09 11:59:59'),
                true,
            ],
            [
                new DateTimeImmutable('2019-12-09 12:00:00'),
                ConsumptionStatus::Unused,
                new DateTimeImmutable('2019-12-09 12:00:00'),
                true,
            ],
            [
                new DateTimeImmutable('2019-12-09 12:00:00'),
                ConsumptionStatus::Unused,
                new DateTimeImmutable('2019-12-09 12:00:01'),
                false,
            ],
            [
                new DateTimeImmutable('2019-12-09 12:00:00'),
                ConsumptionStatus::Consumed,
                new DateTimeImmutable('2019-12-09 11:59:59'),
                false,
            ],
            [
                new DateTimeImmutable('2019-12-09 12:00:00'),
                ConsumptionStatus::Consumed,
                new DateTimeImmutable('2019-12-09 12:00:00'),
                false,
            ],
            [
                new DateTimeImmutable('2019-12-09 12:00:00'),
                ConsumptionStatus::Consumed,
                new DateTimeImmutable('2019-12-09 12:00:01'),
                false,
            ],
        ];
    }
}
