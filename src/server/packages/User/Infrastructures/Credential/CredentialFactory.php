<?php

declare(strict_types=1);

namespace User\Infrastructures\Credential;

use Support\Contracts\UuidGeneratorInterface;
use User\Domain\Models\Credential\AccessTokenFactoryInterface;
use User\Domain\Models\Credential\Credential;
use User\Domain\Models\Credential\CredentialFactoryInterface;
use User\Domain\Models\Credential\CredentialId;
use User\Domain\Models\Credential\IsEnabled;
use User\Domain\Models\Credential\RefreshTokenFactoryInterface;
use User\Domain\Models\UserId;

class CredentialFactory implements CredentialFactoryInterface
{
    public function __construct(
        private readonly UuidGeneratorInterface $uuid,
        private readonly AccessTokenFactoryInterface $accessTokenFactory,
        private readonly RefreshTokenFactoryInterface $refreshTokenFactory,
    ) {
    }

    public function create(string $userId): Credential
    {
        return new Credential(
            $credentialId = new CredentialId($this->uuid->generate()),
            new UserId($userId),
            $this->accessTokenFactory->create($credentialId->value),
            $this->refreshTokenFactory->create(),
            new IsEnabled(true),
        );
    }
}
