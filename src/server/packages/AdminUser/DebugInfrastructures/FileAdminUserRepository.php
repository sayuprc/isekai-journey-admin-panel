<?php

declare(strict_types=1);

namespace AdminUser\DebugInfrastructures;

use AdminUser\Domain\Models\AdminUser;
use AdminUser\Domain\Models\AdminUserId;
use AdminUser\Domain\Models\AdminUserRepositoryInterface;
use AdminUser\Domain\Models\Email;
use AdminUser\Domain\Models\HashedPassword;
use Override;
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

    #[Override]
    public function all(): array
    {
        $adminUsers = $this->loadAll();

        usort($adminUsers, fn (AdminUser $a, AdminUser $b): int => $a->createdAt->value <=> $b->createdAt->value);

        return $adminUsers;
    }

    #[Override]
    public function find(AdminUserId $adminUserId): ?AdminUser
    {
        foreach ($this->loadAll() as $adminUser) {
            if ($adminUser->adminUserId->equals($adminUserId)) {
                return $adminUser;
            }
        }

        return null;
    }

    #[Override]
    public function findByEmail(Email $email): ?AdminUser
    {
        foreach ($this->loadAll() as $adminUser) {
            if ($adminUser->email->equals($email)) {
                return $adminUser;
            }
        }

        return null;
    }

    #[Override]
    public function register(AdminUser $adminUser, HashedPassword $hashedPassword): AdminUser
    {
        $this->store->save(
            $this->filePath,
            [...$adminUser->toArray(), 'hashed_password' => $hashedPassword->value],
            array_keys(
                array_filter(
                    $this->loadAll(),
                    fn (AdminUser $item): bool => $item->equals($adminUser),
                ),
            )[0] ?? null,
        );

        return $adminUser;
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
