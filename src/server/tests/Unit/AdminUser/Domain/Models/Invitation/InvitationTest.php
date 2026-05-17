<?php

declare(strict_types=1);

namespace Tests\Unit\AdminUser\Domain\Models\Invitation;

use AdminUser\Domain\Models\Invitation\ConsumedAt;
use AdminUser\Domain\Models\Invitation\ExpiresAt;
use AdminUser\Domain\Models\Invitation\HashedToken;
use AdminUser\Domain\Models\Invitation\Invitation;
use AdminUser\Domain\Models\Invitation\InvitationId;
use AdminUser\Domain\Models\Permissions;
use AdminUser\Domain\Models\Role;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use Support\Domain\Error\BusinessRuleViolationError;
use Tests\TestCase;

class InvitationTest extends TestCase
{
    private function buildInvitation(
        ?DateTimeImmutable $expiresAt = null,
        ?DateTimeImmutable $consumedAt = null,
    ): Invitation {
        return new Invitation(
            InvitationId::reconstruct('11111111-1111-1111-1111-111111111111'),
            HashedToken::reconstruct(str_repeat('a', 64)),
            Role::General,
            Permissions::reconstruct([]),
            ExpiresAt::reconstruct($expiresAt ?? new DateTimeImmutable('2026-01-02 00:00:00')),
            is_null($consumedAt) ? null : ConsumedAt::reconstruct($consumedAt),
        );
    }

    #[Test]
    public function consumeSucceedsWhenNotExpiredAndNotConsumed(): void
    {
        $invitation = $this->buildInvitation(new DateTimeImmutable('2026-01-02 00:00:00'));
        $now = new DateTimeImmutable('2026-01-01 12:00:00');

        $result = $invitation->consume($now);

        $this->assertTrue($result->isOk());
        $consumed = $result->unwrap();
        $this->assertTrue($consumed->isConsumed());
        $this->assertNotNull($consumed->consumedAt);
        $this->assertEquals($now, $consumed->consumedAt->value);
    }

    #[Test]
    public function consumeFailsWhenExpired(): void
    {
        $invitation = $this->buildInvitation(new DateTimeImmutable('2026-01-01 00:00:00'));
        $now = new DateTimeImmutable('2026-01-02 00:00:00');

        $result = $invitation->consume($now);

        $this->assertTrue($result->isErr());
        $error = $result->unwrapErr();
        $this->assertInstanceOf(BusinessRuleViolationError::class, $error);
        $this->assertSame('この招待トークンは有効期限が切れています', $error->message);
    }

    #[Test]
    public function consumeFailsWhenAlreadyConsumed(): void
    {
        $invitation = $this->buildInvitation(
            new DateTimeImmutable('2026-01-02 00:00:00'),
            new DateTimeImmutable('2026-01-01 10:00:00'),
        );
        $now = new DateTimeImmutable('2026-01-01 12:00:00');

        $result = $invitation->consume($now);

        $this->assertTrue($result->isErr());
        $error = $result->unwrapErr();
        $this->assertInstanceOf(BusinessRuleViolationError::class, $error);
        $this->assertSame('この招待トークンは既に使用されています', $error->message);
    }
}
