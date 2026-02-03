<?php

declare(strict_types=1);

namespace User\DebugInfrastructures;

use Support\DebugInfrastructures\Repository\DebugConfig;
use Support\DebugInfrastructures\Repository\FileStore;
use User\Domain\Models\Email;
use User\Domain\Models\User;
use User\Domain\Models\UserId;
use User\Domain\Models\UserRepositoryInterface;

readonly class FileUserRepository implements UserRepositoryInterface
{
    private const string FILE_NAME = 'users.dat';

    private string $filePath;

    /**
     * @param FileStore<User> $store
     */
    public function __construct(
        private FileStore $store,
        DebugConfig $config,
    ) {
        $this->filePath = $config->path . '/' . self::FILE_NAME;
    }

    public function find(UserId $userId): ?User
    {
        foreach ($this->store->getAll($this->filePath) as $user) {
            if ($user->userId->value === $userId->value) {
                return $user;
            }
        }

        return null;
    }

    public function findByEmail(Email $email): ?User
    {
        foreach ($this->store->getAll($this->filePath) as $user) {
            if ($user->email->value === $email->value) {
                return $user;
            }
        }

        return null;
    }

    public function save(User $user): User
    {
        $this->store->put($this->filePath, $user->userId->value, $user);

        return $user;
    }
}
