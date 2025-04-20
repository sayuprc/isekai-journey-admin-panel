<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Application\Login;

use Auth\Application\Login\LoginInteractor;
use Auth\UseCases\Login\LoginRequest;
use Auth\UseCases\Login\LoginResponse;
use Auth\UseCases\Login\LoginUseCaseInterface;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Auth\StatefulGuard;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LoginInteractorTest extends TestCase
{
    private AuthManager&MockInterface $authManager;

    private LoginInteractor $interactor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authManager = Mockery::mock(AuthManager::class);

        $this->interactor = new LoginInteractor($this->authManager);
    }

    #[Test]
    public function isImplementsSpecificInterface(): void
    {
        $this->assertInstanceOf(LoginUseCaseInterface::class, $this->interactor);
    }

    #[Test]
    public function successLoginAttempt(): void
    {
        $this->authManager->shouldReceive('guard')
            ->andReturnUsing(function (): MockInterface&StatefulGuard {
                $guard = Mockery::mock(StatefulGuard::class);

                $guard->shouldReceive('attempt')
                    ->with([
                        'email' => 'user@example.com',
                        'password' => 'plain password',
                    ])
                    ->andReturn(true)
                    ->once();

                return $guard;
            })
            ->once();

        $request = new LoginRequest([
            'email' => 'user@example.com',
            'password' => 'plain password',
        ]);

        $response = $this->interactor->handle($request);

        $this->assertInstanceOf(LoginResponse::class, $response);
        $this->assertTrue($response->isSucceeded);
    }

    #[Test]
    public function failureLoginAttempt(): void
    {
        $this->authManager->shouldReceive('guard')
            ->andReturnUsing(function (): MockInterface&StatefulGuard {
                $guard = Mockery::mock(StatefulGuard::class);

                $guard->shouldReceive('attempt')
                    ->with([
                        'email' => 'user@example.com',
                        'password' => 'plain password',
                    ])
                    ->andReturn(false)
                    ->once();

                return $guard;
            })
            ->once();

        $request = new LoginRequest([
            'email' => 'user@example.com',
            'password' => 'plain password',
        ]);

        $response = $this->interactor->handle($request);

        $this->assertInstanceOf(LoginResponse::class, $response);
        $this->assertFalse($response->isSucceeded);
    }
}
