<?php

declare(strict_types=1);

namespace AdminUser\Domain\Models\Invitation;

interface InvitationRepositoryInterface
{
    public function save(Invitation $invitation): Invitation;

    public function findByHashedToken(HashedToken $hashedToken): ?Invitation;
}
