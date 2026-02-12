<?php

declare(strict_types=1);

namespace AdminUser\DebugInfrastructures;

use AdminUser\Domain\Models\AdminUser;
use AdminUser\Domain\Models\AdminUserId;
use AdminUser\Domain\Models\AdminUserRepositoryInterface;
use AdminUser\Domain\Models\Email;
use Support\DebugInfrastructures\Repository\DebugConfig;
use Support\DebugInfrastructures\Repository\FileStore;

readonly class FileAdminUserRepository implements AdminUserRepositoryInterface
{
    private const string FILE_NAME = 'admin-users.json';

    private string $filePath;

    /**
     * @param FileStore<AdminUser> $store
     */
    public function __construct(
        private FileStore $store,
        DebugConfig $config,
    ) {
        $this->filePath = $config->path . '/' . self::FILE_NAME;
    }

    public function find(AdminUserId $userId): ?AdminUser
    {
        foreach ($this->store->getAll($this->filePath, AdminUser::class) as $user) {
            if ($user->userId->value === $userId->value) {
                return $user;
            }
        }

        return null;
    }

    public function findByEmail(Email $email): ?AdminUser
    {
        foreach ($this->store->getAll($this->filePath, AdminUser::class) as $user) {
            if ($user->email->value === $email->value) {
                return $user;
            }
        }

        return null;
    }

    public function save(AdminUser $user): AdminUser
    {
        $this->store->put($this->filePath, $user->userId->value, $user, AdminUser::class);

        return $user;
    }
}
