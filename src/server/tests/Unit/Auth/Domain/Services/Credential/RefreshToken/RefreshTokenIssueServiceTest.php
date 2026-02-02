<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Domain\Services\Credential\RefreshToken;

use Auth\Domain\Models\Credential\RefreshToken\ConsumptionStatus;
use Auth\Domain\Models\Credential\RefreshToken\ExpiredAt;
use Auth\Domain\Models\Credential\RefreshToken\RefreshTokenFactoryInterface;
use Auth\Domain\Models\Credential\RefreshToken\RefreshTokenId;
use Auth\Domain\Models\Credential\RefreshToken\TokenValue;
use Auth\Domain\Services\Credential\RefreshToken\RandomTokenGeneratorInterface;
use Auth\Domain\Services\Credential\RefreshToken\RefreshTokenIssueService;
use DateTimeImmutable;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Support\Contracts\ClockInterface;
use Support\Contracts\UuidGeneratorInterface;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;
use User\Domain\Models\UserId;

class RefreshTokenIssueServiceTest extends TestCase
{
    use EntityFactory;

    private ClockInterface&MockInterface $clock;

    private MockInterface&UuidGeneratorInterface $uuidGenerator;

    private MockInterface&RandomTokenGeneratorInterface $randomTokenGenerator;

    private MockInterface&RefreshTokenFactoryInterface $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clock = Mockery::mock(ClockInterface::class);
        $this->uuidGenerator = Mockery::mock(UuidGeneratorInterface::class);
        $this->randomTokenGenerator = Mockery::mock(RandomTokenGeneratorInterface::class);
        $this->factory = Mockery::mock(RefreshTokenFactoryInterface::class);
    }

    #[Test]
    public function issue(): void
    {
        $userIdStr = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $generatedUuid = 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB';
        $generatedToken = 'random-token-value-12345678901234567890123456789012';
        $now = new DateTimeImmutable('2026-02-01 00:00:00');

        $this->clock->shouldReceive('now')
            ->with()
            ->andReturn($now)
            ->once();

        $this->uuidGenerator->shouldReceive('generate')
            ->with()
            ->andReturn($generatedUuid)
            ->once();

        $this->randomTokenGenerator->shouldReceive('generate')
            ->with()
            ->andReturn($generatedToken)
            ->once();

        $expectedExpiredAt = $now->modify('+7 days');

        $expectedRefreshToken = $this->createRefreshToken(
            $generatedUuid,
            $userIdStr,
            $generatedToken,
            $expectedExpiredAt,
            ConsumptionStatus::Unused,
        );

        $this->factory->shouldReceive('create')
            ->withArgs(
                fn (
                    RefreshTokenId $id,
                    UserId $userId,
                    TokenValue $token,
                    ExpiredAt $expiredAt,
                    ConsumptionStatus $status,
                ): bool => $id->value === $generatedUuid
                    && $userId->value === $userIdStr
                    && $token->value === $generatedToken
                    && $expiredAt->value->getTimestamp() === $expectedExpiredAt->getTimestamp()
                    && $status === ConsumptionStatus::Unused,
            )
            ->andReturn($expectedRefreshToken)
            ->once();

        $result = $this->getInstance()->issue($userIdStr);

        $this->assertTrue($result->isOk());
        $this->assertSame($expectedRefreshToken, $result->unwrap());
    }

    private function getInstance(): RefreshTokenIssueService
    {
        return new RefreshTokenIssueService(
            $this->clock,
            $this->uuidGenerator,
            $this->randomTokenGenerator,
            $this->factory,
        );
    }
}
