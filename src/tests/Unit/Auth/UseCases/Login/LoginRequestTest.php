<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\UseCases\Login;

use Auth\UseCases\Login\LoginRequest;
use Exception;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LoginRequestTest extends TestCase
{
    #[Test]
    public function properlyStoresValue(): void
    {
        $instance = new LoginRequest([
            'email' => 'user@example.com',
            'password' => 'plain password',
        ]);

        $this->assertSame(
            [
                'email' => 'user@example.com',
                'password' => 'plain password',
            ],
            $instance->credentials
        );
    }

    #[Test]
    #[DataProvider('provideThrowExceptionWhenInvalidValue')]
    public function throwExceptionWhenInvalidValue(array $array): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid credentials');

        new LoginRequest($array);
    }

    public static function provideThrowExceptionWhenInvalidValue(): array
    {
        return [
            [[]],
            [['email' => 'user@example.com']],
            [['password' => 'plain password']],
        ];
    }
}
