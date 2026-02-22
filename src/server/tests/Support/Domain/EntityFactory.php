<?php

declare(strict_types=1);

namespace Tests\Support\Domain;

use AdminUser\DebugInfrastructures\FileAdminUserRepository;
use AdminUser\Domain\Models\AdminUser;
use AdminUser\Domain\Models\AdminUserId;
use AdminUser\Domain\Models\Role;
use Auth\DebugInfrastructures\FileRefreshTokenRepository;
use Auth\Domain\Models\Token\AccessToken\AccessToken;
use Auth\Domain\Models\Token\AccessToken\Jwt;
use Auth\Domain\Models\Token\RefreshToken\ConsumptionStatus;
use Auth\Domain\Models\Token\RefreshToken\ExpiredAt;
use Auth\Domain\Models\Token\RefreshToken\RefreshToken;
use Auth\Domain\Models\Token\RefreshToken\RefreshTokenId;
use Auth\Domain\Models\Token\RefreshToken\TokenValue;
use Creator\DebugInfrastructures\FileCreatorRepository;
use Creator\Domain\Models\Creator;
use Creator\Domain\Models\CreatorId;
use Creator\Domain\Models\CreatorName;
use DateTimeImmutable;
use Performer\DebugInfrastructures\FilePerformerRepository;
use Performer\Domain\Models\Performer;
use Performer\Domain\Models\PerformerId;
use Performer\Domain\Models\PerformerName;
use Song\DebugInfrastructures\FileSongRepository;
use Song\Domain\Models\Creators\Arrangers;
use Song\Domain\Models\Creators\Composers;
use Song\Domain\Models\Creators\Lyricists;
use Song\Domain\Models\Description;
use Song\Domain\Models\Song;
use Song\Domain\Models\SongId;
use Song\Domain\Models\Title;
use SongType\Domain\Models\SongType;
use Support\Domain\ValueObjects\OrderNo;

trait EntityFactory
{
    protected function createCreator(string $creatorId, string $creatorName): Creator
    {
        return new Creator(
            CreatorId::reconstruct($creatorId),
            CreatorName::reconstruct($creatorName),
        );
    }

    protected function storeCreators(Creator ...$creators): void
    {
        array_map(
            fn (Creator $creator) => $this->factory(FileCreatorRepository::class, $creator->toArray()),
            $creators,
        );
    }

    protected function createPerformer(string $performerId, string $performerName, int $orderNo): Performer
    {
        return new Performer(
            PerformerId::reconstruct($performerId),
            PerformerName::reconstruct($performerName),
            OrderNo::reconstruct($orderNo),
        );
    }

    protected function storePerformers(Performer ...$performers): void
    {
        array_map(
            fn (Performer $performer) => $this->factory(FilePerformerRepository::class, $performer->toArray()),
            $performers,
        );
    }

    /**
     * @param array<array{creatorId: string, orderNo: int}> $arrangers
     * @param array<array{creatorId: string, orderNo: int}> $composers
     * @param array<array{creatorId: string, orderNo: int}> $lyricists
     */
    protected function createSong(
        string $songId,
        string $title,
        string $description,
        SongType $songType,
        int $orderNo,
        array $arrangers,
        array $composers,
        array $lyricists,
    ): Song {
        return new Song(
            SongId::reconstruct($songId),
            Title::reconstruct($title),
            Description::reconstruct($description),
            $songType,
            OrderNo::reconstruct($orderNo),
            Arrangers::fromArray($arrangers)->unwrap(),
            Composers::fromArray($composers)->unwrap(),
            Lyricists::fromArray($lyricists)->unwrap(),
        );
    }

    private function storeSongs(Song ...$songs): void
    {
        array_map(
            fn (Song $song) => $this->factory(FileSongRepository::class, $song->toArray()),
            $songs,
        );
    }

    protected function createUser(
        string $userId,
        string $email,
        Role $role = Role::General,
        array $permissions = [],
        ?DateTimeImmutable $createdAt = null,
        string $adminUserName = 'テストユーザー',
    ): AdminUser {
        return AdminUser::reconstruct(
            $userId,
            $adminUserName,
            $email,
            $createdAt ?? new DateTimeImmutable(),
            $role->value,
            $permissions,
        );
    }

    protected function storeUsers(AdminUser ...$users): void
    {
        array_map(
            fn (AdminUser $user) => $this->factory(FileAdminUserRepository::class, $user->toArray()),
            $users,
        );
    }

    protected function createAccessToken(string $jwt): AccessToken
    {
        return new AccessToken(Jwt::reconstruct($jwt));
    }

    protected function createRefreshToken(
        string $refreshTokenId,
        string $userId,
        string $tokenValue,
        DateTimeImmutable $expiredAt,
        ConsumptionStatus $status,
    ): RefreshToken {
        return new RefreshToken(
            RefreshTokenId::reconstruct($refreshTokenId),
            AdminUserId::reconstruct($userId),
            TokenValue::reconstruct($tokenValue),
            ExpiredAt::reconstruct($expiredAt),
            $status,
        );
    }

    protected function storeRefreshTokens(RefreshToken ...$refreshTokens): void
    {
        array_map(
            fn (RefreshToken $refreshToken) => $this->factory(FileRefreshTokenRepository::class, $refreshToken->toArray()),
            $refreshTokens,
        );
    }
}
