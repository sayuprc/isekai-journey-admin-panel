<?php

declare(strict_types=1);

namespace Tests\Support\Domain;

use AdminUser\DebugInfrastructures\FileAdminUserRepository;
use AdminUser\Domain\Models\AdminUser;
use AdminUser\Domain\Models\AdminUserRepositoryInterface;
use AdminUser\Domain\Models\HashedPassword;
use Auth\DebugInfrastructures\FileRefreshTokenRepository;
use Auth\Domain\Models\Token\RefreshToken\RefreshToken;
use Auth\Domain\Models\Token\RefreshToken\RefreshTokenRepositoryInterface;
use Creator\DebugInfrastructures\FileCreatorRepository;
use Creator\Domain\Models\Creator;
use Creator\Domain\Models\CreatorRepositoryInterface;
use Performer\DebugInfrastructures\FilePerformerRepository;
use Performer\Domain\Models\Performer;
use Performer\Domain\Models\PerformerRepositoryInterface;
use Song\DebugInfrastructures\FileSongRepository;
use Song\Domain\Models\Song;
use Song\Domain\Models\SongRepositoryInterface;
use Song\Domain\Models\Tag;
use Song\Domain\Models\TagRepositoryInterface;

trait EntityStore
{
    protected function storeCreators(Creator ...$items): void
    {
        if (method_exists($this, 'factory')) {
            array_map(
                fn (Creator $item) => $this->factory(FileCreatorRepository::class, $item->toArray()),
                $items,
            );

            return;
        }

        $repository = $this->makeRepository(CreatorRepositoryInterface::class);
        array_map(fn (Creator $item) => $repository->save($item), $items);
    }

    protected function storePerformers(Performer ...$items): void
    {
        if (method_exists($this, 'factory')) {
            array_map(
                fn (Performer $item) => $this->factory(FilePerformerRepository::class, $item->toArray()),
                $items,
            );

            return;
        }

        $repository = $this->makeRepository(PerformerRepositoryInterface::class);
        array_map(fn (Performer $item) => $repository->save($item), $items);
    }

    protected function storeSongs(Song ...$items): void
    {
        if (method_exists($this, 'factory')) {
            array_map(
                fn (Song $item) => $this->factory(FileSongRepository::class, $item->toArray()),
                $items,
            );

            return;
        }

        $repository = $this->makeRepository(SongRepositoryInterface::class);
        array_map(fn (Song $item) => $repository->save($item), $items);
    }

    protected function storeSongTags(Tag ...$items): void
    {
        $repository = $this->makeRepository(TagRepositoryInterface::class);
        array_map(fn (Tag $item) => $repository->save($item), $items);
    }

    protected function storeAdminUsers(AdminUser ...$items): void
    {
        if (method_exists($this, 'factory')) {
            array_map(
                fn (AdminUser $user) => $this->factory(FileAdminUserRepository::class, $user->toArray()),
                $items,
            );

            return;
        }

        $repository = $this->makeRepository(AdminUserRepositoryInterface::class);
        array_map(
            fn (AdminUser $item) => $repository->register($item, HashedPassword::reconstruct('hashed-password')),
            $items,
        );
    }

    protected function storeRefreshTokens(RefreshToken ...$items): void
    {
        if (method_exists($this, 'factory')) {
            array_map(
                fn (RefreshToken $item) => $this->factory(FileRefreshTokenRepository::class, $item->toArray()),
                $items,
            );

            return;
        }

        $repository = $this->makeRepository(RefreshTokenRepositoryInterface::class);
        array_map(fn (RefreshToken $refreshToken) => $repository->save($refreshToken), $items);
    }

    /**
     * @template T
     *
     * @param class-string<T> $class
     *
     * @return T
     */
    private function makeRepository(string $class): mixed
    {
        return $this->app->make($class);
    }
}
