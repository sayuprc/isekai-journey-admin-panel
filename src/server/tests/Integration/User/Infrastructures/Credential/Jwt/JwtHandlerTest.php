<?php

declare(strict_types=1);

namespace Tests\Integration\User\Infrastructures\Credential\Jwt;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use User\Infrastructures\Credential\Jwt\AccessTokenPayload;
use User\Infrastructures\Credential\Jwt\JwtHandler;

class JwtHandlerTest extends TestCase
{
    #[Test]
    public function generateJwtSuccessfully(): void
    {
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
        return $this->app->make(JwtHandler::class);
    }
}
