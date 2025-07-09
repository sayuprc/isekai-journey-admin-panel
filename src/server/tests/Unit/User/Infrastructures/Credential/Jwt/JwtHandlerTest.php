<?php

declare(strict_types=1);

namespace Tests\Unit\User\Infrastructures\Credential\Jwt;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Support\Contracts\ConfigInterface;
use Tests\TestCase;
use User\Domain\Services\Jwt\AccessTokenPayload;
use User\Infrastructures\Credential\Jwt\JwtHandler;

class JwtHandlerTest extends TestCase
{
    private ConfigInterface&MockInterface $config;

    protected function setUp(): void
    {
        parent::setUp();

        $this->config = Mockery::mock(ConfigInterface::class);
    }

    #[Test]
    public function generateJwtSuccessfully(): void
    {
        $this->config->shouldReceive('getString')
            ->with('auth.jwt.alg')
            ->andReturn('HS256')
            ->once();

        $this->config->shouldReceive('getString')
            ->with('auth.jwt.key')
            ->andReturn('key')
            ->once();

        $jwt = $this->getInstance()->generate(new AccessTokenPayload(
            iss: 'iss',
            iat: 0,
            exp: 180,
            nbf: 180,
            jti: 'jti'
        ));

        $elements = explode('.', $jwt);
        $payload = json_decode(base64_decode($elements[1]), true);

        $this->assertSame([
            'iss' => 'iss',
            'iat' => 0,
            'exp' => 180,
            'nbf' => 180,
            'jti' => 'jti',
        ], $payload);
    }

    private function getInstance(): JwtHandler
    {
        return new JwtHandler($this->config);
    }
}
