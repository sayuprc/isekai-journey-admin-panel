<?php

declare(strict_types=1);

namespace Auth\Infrastructures;

use Auth\Domain\Models\AdminUserPasskey;
use ParagonIE\ConstantTime\Base64UrlSafe;
use Symfony\Component\Uid\Uuid;
use Webauthn\CredentialRecord;
use Webauthn\PublicKeyCredentialDescriptor;
use Webauthn\TrustPath\EmptyTrustPath;

readonly class PasskeyCredentialRecordConverter
{
    public function toCredentialRecord(AdminUserPasskey $passkey): CredentialRecord
    {
        return CredentialRecord::create(
            Base64UrlSafe::decodeNoPadding($passkey->credentialId),
            PublicKeyCredentialDescriptor::CREDENTIAL_TYPE_PUBLIC_KEY,
            [],
            'none',
            EmptyTrustPath::create(),
            Uuid::v4(),
            Base64UrlSafe::decodeNoPadding($passkey->publicKey),
            $passkey->adminUserId,
            $passkey->signCount,
        );
    }
}
