<?php

declare(strict_types=1);

namespace AdminUser\DebugInfrastructures;

use AdminUser\Domain\Models\AdminUser;
use AdminUser\Domain\Models\AdminUserId;
use AdminUser\Domain\Models\AdminUserRepositoryInterface;
use AdminUser\Domain\Models\Email;
use AdminUser\Domain\Models\HashedPassword;
use Support\Contracts\MapperInterface;
use Support\DebugInfrastructures\Repository\DebugConfig;
use Support\DebugInfrastructures\Repository\JsonFileStore;

readonly class FileAdminUserRepository implements AdminUserRepositoryInterface
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

    public function find(AdminUserId $userId): ?AdminUser
    {
        foreach ($this->loadAll() as $user) {
            if ($user->userId->equals($userId)) {
                return $user;
            }
        }

        return null;
    }

    public function findByEmail(Email $email): ?AdminUser
    {
        foreach ($this->loadAll() as $user) {
            if ($user->email->equals($email)) {
                return $user;
            }
        }

        return null;
    }

    public function register(AdminUser $user, HashedPassword $hashedPassword): AdminUser
    {
        $this->store->save(
            $this->filePath,
            [...$user->toArray(), 'hashed_password' => $hashedPassword->value],
            array_keys(
                array_filter(
                    $this->loadAll(),
                    fn (AdminUser $item): bool => $item->equals($user),
                ),
            )[0] ?? null,
        );

        return $user;
    }

    /**
     * @return array<AdminUser>
     */
    private function loadAll(): array
    {
        $class = AdminUser::class;

        /** @var array<AdminUser> */
        return $this->mapper->map("array<{$class}>", $this->store->load($this->filePath));
    }
}
