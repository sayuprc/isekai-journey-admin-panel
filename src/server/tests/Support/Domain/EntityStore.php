<?php

declare(strict_types=1);

namespace Tests\Support\Domain;

use AdminUser\Domain\Models\AdminUser;
use AdminUser\Domain\Models\AdminUserRepositoryInterface;
use AdminUser\Domain\Models\HashedPassword;
use Auth\Domain\Models\Token\RefreshToken\RefreshToken;
use Auth\Domain\Models\Token\RefreshToken\RefreshTokenRepositoryInterface;
use Creator\Domain\Models\Creator;
use Creator\Domain\Models\CreatorRepositoryInterface;
use Performer\Domain\Models\Performer;
use Performer\Domain\Models\PerformerRepositoryInterface;
use Person\Domain\Models\Person;
use Person\Domain\Models\PersonRepositoryInterface;
use Song\Domain\Models\Song;
use Song\Domain\Models\SongRepositoryInterface;
use Song\Domain\Models\Tag\SongTag;
use Song\Domain\Models\Tag\SongTagRepositoryInterface;

trait EntityStore
{
    protected function storeCreators(Creator ...$items): void
    {
        $repository = $this->makeRepository(CreatorRepositoryInterface::class);
        array_map(fn (Creator $item) => $repository->save($item), $items);
    }

    protected function storePerformers(Performer ...$items): void
    {
        $repository = $this->makeRepository(PerformerRepositoryInterface::class);
        array_map(fn (Performer $item) => $repository->save($item), $items);
    }

    protected function storePersons(Person ...$items): void
    {
        $repository = $this->makeRepository(PersonRepositoryInterface::class);
        array_map(fn (Person $item) => $repository->save($item), $items);
    }

    protected function storeSongs(Song ...$items): void
    {
        $repository = $this->makeRepository(SongRepositoryInterface::class);
        array_map(fn (Song $item) => $repository->save($item), $items);
    }

    protected function storeSongTags(SongTag ...$items): void
    {
        $repository = $this->makeRepository(SongTagRepositoryInterface::class);
        array_map(fn (SongTag $item) => $repository->save($item), $items);
    }

    protected function storeAdminUsers(AdminUser ...$items): void
    {
        $repository = $this->makeRepository(AdminUserRepositoryInterface::class);
        array_map(
            fn (AdminUser $item) => $repository->register($item, HashedPassword::reconstruct('hashed-password')),
            $items,
        );
    }

    protected function storeRefreshTokens(RefreshToken ...$items): void
    {
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
