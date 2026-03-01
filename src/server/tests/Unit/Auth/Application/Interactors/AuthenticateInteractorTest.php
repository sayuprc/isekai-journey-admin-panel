<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Application\Interactors;

use AdminUser\Domain\Models\AdminUserId;
use AdminUser\Domain\Models\AdminUserRepositoryInterface;
use AdminUser\Domain\Models\Role;
use Auth\Application\Interactors\AuthenticateInteractor;
use Auth\Application\UseCase\Authenticate\AuthenticateInputData;
use Auth\Domain\Models\AuthContext;
use Auth\Domain\Models\Token\RefreshToken\ConsumptionStatus;
use Auth\Domain\Models\Token\RefreshToken\RefreshTokenId;
use Auth\Domain\Models\Token\RefreshToken\RefreshTokenRepositoryInterface;
use Auth\Domain\Services\Token\AccessToken\AccessTokenPayload;
use Auth\Domain\Services\Token\AccessToken\JwtHandlerInterface;
use DateTimeImmutable;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use ResultType\Err;
use ResultType\Ok;
use Support\Domain\Error\EntityRuleViolationError;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class AuthenticateInteractorTest extends TestCase
{
    use EntityFactory;

    private JwtHandlerInterface&MockInterface $jwtHandler;

    private MockInterface&RefreshTokenRepositoryInterface $refreshTokenRepository;

    private AdminUserRepositoryInterface&MockInterface $userRepository;

    private AuthContext $context;

    protected function setUp(): void
    {
        parent::setUp();

        $this->jwtHandler = Mockery::mock(JwtHandlerInterface::class);
        $this->refreshTokenRepository = Mockery::mock(RefreshTokenRepositoryInterface::class);
        $this->userRepository = Mockery::mock(AdminUserRepositoryInterface::class);
        $this->context = new AuthContext();
    }

    #[Test]
    public function canAuthenticate(): void
    {
        $refreshTokenId = $this->generateUuid();

        $this->jwtHandler->shouldReceive('verify')
            ->with('access_token')
            ->andReturn(new Ok(new AccessTokenPayload('', 0, 0, 0, $refreshTokenId)))
            ->once();

        $adminUserId = $this->generateUuid();

        $this->refreshTokenRepository->shouldReceive('findActive')
            ->withArgs(fn (RefreshTokenId $arg) => $arg->value === $refreshTokenId)
            ->andReturn(
                $this->createRefreshToken(
                    $refreshTokenId,
                    $adminUserId,
                    'token',
                    new DateTimeImmutable(),
                    ConsumptionStatus::Unused,
                ),
            )
            ->once();

        $this->userRepository->shouldReceive('find')
            ->withArgs(fn (AdminUserId $arg) => $arg->value === $adminUserId)
            ->andReturn($this->createAdminUser($adminUserId, 'example@example.com', Role::General, []))
            ->once();

        $result = $this->getInstance()->handle(new AuthenticateInputData('access_token'));

        $this->assertTrue($result->isOk());
    }

    #[Test]
    public function unauthenticatedWhenExpireToken(): void
    {
        $this->jwtHandler->shouldReceive('verify')
            ->with('access_token')
            ->andReturn(new Err(new EntityRuleViolationError('exp', '期限切れです')))
            ->once();

        $result = $this->getInstance()->handle(new AuthenticateInputData('access_token'));

        $this->assertTrue($result->isErr());
    }

    #[Test]
    public function unauthenticatedWhenCredentialNotFound(): void
    {
        $refreshTokenId = $this->generateUuid();

        $this->jwtHandler->shouldReceive('verify')
            ->with('access_token')
            ->andReturn(new Ok(new AccessTokenPayload('', 0, 0, 0, $refreshTokenId)))
            ->once();

        $this->refreshTokenRepository->shouldReceive('findActive')
            ->withArgs(fn (RefreshTokenId $arg) => $arg->value === $refreshTokenId)
            ->andReturnNull()
            ->once();

        $result = $this->getInstance()->handle(new AuthenticateInputData('access_token'));

        $this->assertTrue($result->isErr());
    }

    #[Test]
    public function unauthenticatedWhenUserNotFound(): void
    {
        $refreshTokenId = $this->generateUuid();

        $this->jwtHandler->shouldReceive('verify')
            ->with('access_token')
            ->andReturn(new Ok(new AccessTokenPayload('', 0, 0, 0, $refreshTokenId)))
            ->once();

        $adminUserId = $this->generateUuid();

        $this->refreshTokenRepository->shouldReceive('findActive')
            ->withArgs(fn (RefreshTokenId $arg) => $arg->value === $refreshTokenId)
            ->andReturn(
                $this->createRefreshToken(
                    $refreshTokenId,
                    $adminUserId,
                    'token',
                    new DateTimeImmutable(),
                    ConsumptionStatus::Unused,
                ),
            )
            ->once();

        $this->userRepository->shouldReceive('find')
            ->withArgs(fn (AdminUserId $arg) => $arg->value === $adminUserId)
            ->andReturnNull()
            ->once();

        $result = $this->getInstance()->handle(new AuthenticateInputData('access_token'));

        $this->assertTrue($result->isErr());
    }

    private function getInstance(): AuthenticateInteractor
    {
        return new AuthenticateInteractor(
            $this->jwtHandler,
            $this->refreshTokenRepository,
            $this->userRepository,
            $this->context,
        );
    }
}
