<?php

declare(strict_types=1);

namespace Auth\Domain\Models;

use DateTimeImmutable;
use ParagonIE\ConstantTime\Base64UrlSafe;
use Webauthn\CredentialRecord;
use Webauthn\PublicKeyCredentialDescriptor;
use Webauthn\TrustPath\EmptyTrustPath;

readonly class AdminUserPasskey
{
    public function __construct(
        public string $adminUserPasskeyId,
        public string $adminUserId,
        public string $credentialId,
        public string $publicKey,
        public int $signCount,
        public DateTimeImmutable $createdAt,
        public ?DateTimeImmutable $lastUsedAt,
    ) {
    }

    public static function fromCredentialRecord(
        string $adminUserPasskeyId,
        string $adminUserId,
        CredentialRecord $credentialRecord,
        DateTimeImmutable $createdAt,
        ?DateTimeImmutable $lastUsedAt = null,
    ): self {
        return new self(
            $adminUserPasskeyId,
            $adminUserId,
            Base64UrlSafe::encodeUnpadded($credentialRecord->publicKeyCredentialId),
            Base64UrlSafe::encodeUnpadded($credentialRecord->credentialPublicKey),
            $credentialRecord->counter,
            $createdAt,
            $lastUsedAt,
        );
    }

    public function toCredentialRecord(): CredentialRecord
    {
        return CredentialRecord::create(
            Base64UrlSafe::decodeNoPadding($this->credentialId),
            PublicKeyCredentialDescriptor::CREDENTIAL_TYPE_PUBLIC_KEY,
            [],
            'none',
            EmptyTrustPath::create(),
            \Symfony\Component\Uid\Uuid::v4(),
            Base64UrlSafe::decodeNoPadding($this->publicKey),
            $this->adminUserId,
            $this->signCount,
        );
    }

    public function withCounter(int $signCount, DateTimeImmutable $lastUsedAt): self
    {
        return new self(
            $this->adminUserPasskeyId,
            $this->adminUserId,
            $this->credentialId,
            $this->publicKey,
            $signCount,
            $this->createdAt,
            $lastUsedAt,
        );
    }
}
