<?php

declare(strict_types=1);

namespace Auth\DebugInfrastructures;

use AdminUser\Domain\Models\AdminUserId;
use AdminUser\Domain\Models\Email;
use Auth\Domain\Models\AuthAdminUserRepositoryInterface;
use Auth\Domain\Models\AuthenticatableAdminUser;
use Support\Contracts\MapperInterface;
use Support\DebugInfrastructures\Repository\DebugConfig;
use Support\DebugInfrastructures\Repository\JsonFileStore;

class FileAuthAdminUserRepository implements AuthAdminUserRepositoryInterface
{
    private const string FILE_NAME = 'admin-users';

    private string $filePath;

    public function __construct(
        private MapperInterface $mapper,
        private JsonFileStore $store,
        DebugConfig $config,
    ) {
        $this->filePath = $config->path . '/' . self::FILE_NAME;
    }

    public function find(AdminUserId $userId): ?AuthenticatableAdminUser
    {
        foreach ($this->loadAll() as $adminUser) {
            if ($adminUser->userId->equals($userId)) {
                return $adminUser;
            }
        }

        return null;
    }

    public function findByEmail(Email $email): ?AuthenticatableAdminUser
    {
        foreach ($this->store->load($this->filePath) as $item) {
            if (! is_array($item) || ($item['email'] ?? null) !== $email->value) {
                continue;
            }

            return $this->mapper->map(AuthenticatableAdminUser::class, $item);
        }

        return null;
    }

    /**
     * @return array<AuthenticatableAdminUser>
     */
    private function loadAll(): array
    {
        $class = AuthenticatableAdminUser::class;

        /** @var array<AuthenticatableAdminUser> */
        return $this->mapper->map("array<{$class}>", $this->store->load($this->filePath));
    }
}
