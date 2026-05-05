<?php

declare(strict_types=1);

namespace Tests\Integration\Auth\Application\UseCase;

use App\Models\Auth\RefreshToken as AuthRefreshToken;
use Auth\Application\UseCase\Login\LoginInputData;
use Auth\Application\UseCase\Login\LoginUseCase;
use Auth\Domain\Models\Token\RefreshToken\ConsumptionStatus;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\Test;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditTargetType;
use Support\Contracts\Uuid\UuidConverterInterface;
use Tests\Support\Concerns\AssertsAuditLog;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class LoginUseCaseTest extends DatabaseTestCase
{
    use AssertsAuditLog;
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function canLogin(): void
    {
        config()->set([
            'auth.jwt.alg' => 'HS256',
            'auth.jwt.key' => str_repeat('k', 256),
        ]);

        CarbonImmutable::setTestNow('2019-12-02 12:34:29');

        $now = new CarbonImmutable();

        $adminUserId = 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa';

        $this->storeAdminUsers($this->createAdminUser($adminUserId, 'example@example.com'));

        $this->getInstance()->handle(new LoginInputData($adminUserId));

        $refreshTokens = AuthRefreshToken::query()->get()->all();
        $this->assertCount(1, $refreshTokens);
        $converter = $this->app->make(UuidConverterInterface::class);
        $this->assertSame($adminUserId, $converter->toUuid(array_first($refreshTokens)->admin_user_id));
        $this->assertSame(ConsumptionStatus::Unused->value, array_first($refreshTokens)->status);
        $this->assertSame($now->addDays(7)->format('Y-m-d H:i:s'), array_first($refreshTokens)->expired_at->format('Y-m-d H:i:s'));

        $this->assertAuditLogCount(1);
        $log = $this->findAuditLog(AuditAction::Login, AuditTargetType::AdminUser, $adminUserId);
        $this->assertSame($adminUserId, $log['admin_user_id']);
        $this->assertArrayHasKey('refreshTokenId', $log['snapshot']);
        $this->assertArrayNotHasKey('password', $log['snapshot']);
    }

    private function getInstance(): LoginUseCase
    {
        return $this->app->make(LoginUseCase::class);
    }
}
