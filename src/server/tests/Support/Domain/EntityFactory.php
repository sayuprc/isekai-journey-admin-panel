<?php

declare(strict_types=1);

namespace Tests\Support\Domain;

use AdminUser\Domain\Models\AdminUser;
use AdminUser\Domain\Models\AdminUserId;
use AdminUser\Domain\Models\Role;
use Auth\Domain\Models\Token\AccessToken\AccessToken;
use Auth\Domain\Models\Token\AccessToken\Jwt;
use Auth\Domain\Models\Token\RefreshToken\ConsumptionStatus;
use Auth\Domain\Models\Token\RefreshToken\ExpiredAt;
use Auth\Domain\Models\Token\RefreshToken\HashedTokenValue;
use Auth\Domain\Models\Token\RefreshToken\RefreshToken;
use Auth\Domain\Models\Token\RefreshToken\RefreshTokenId;
use Creator\Domain\Models\Creator;
use Creator\Domain\Models\CreatorId;
use Creator\Domain\Models\CreatorName;
use DateTimeImmutable;
use Performer\Domain\Models\Performer;
use Performer\Domain\Models\PerformerId;
use Performer\Domain\Models\PerformerName;
use Song\Domain\Models\Creators\Arrangers;
use Song\Domain\Models\Creators\Composers;
use Song\Domain\Models\Creators\Lyricists;
use Song\Domain\Models\Description;
use Song\Domain\Models\LyricsLink;
use Song\Domain\Models\Song;
use Song\Domain\Models\SongId;
use Song\Domain\Models\SongType;
use Song\Domain\Models\Tag\SongTag;
use Song\Domain\Models\Tag\SongTagId;
use Song\Domain\Models\Tag\SongTagName;
use Song\Domain\Models\Tags\SongTagReferences;
use Song\Domain\Models\Title;
use Support\Domain\ValueObjects\OrderNo;

trait EntityFactory
{
    protected function createCreator(string $creatorId, string $name, int $orderNo): Creator
    {
        return new Creator(
            CreatorId::reconstruct($creatorId),
            CreatorName::reconstruct($name),
            OrderNo::reconstruct($orderNo),
        );
    }

    protected function createPerformer(string $performerId, string $name, int $orderNo): Performer
    {
        return new Performer(
            PerformerId::reconstruct($performerId),
            PerformerName::reconstruct($name),
            OrderNo::reconstruct($orderNo),
        );
    }

    protected function createSong(
        string $songId,
        string $title,
        string $description,
        mixed $lyricsLinkOrType,
        mixed $typeOrIsDisplay = null,
        mixed $isDisplayOrOrderNo = true,
        mixed $orderNoOrTags = 1,
        mixed $tagsOrLyricists = [],
        mixed $lyricistsOrComposers = [],
        mixed $composersOrArrangers = [],
        mixed $arrangers = [],
    ): Song {
        if ($lyricsLinkOrType instanceof SongType) {
            $lyricsLink = null;
            $type = $lyricsLinkOrType;
            $isDisplay = is_bool($typeOrIsDisplay) ? $typeOrIsDisplay : true;
            $orderNo = is_int($isDisplayOrOrderNo) ? $isDisplayOrOrderNo : 1;
            $tags = is_array($orderNoOrTags) ? $orderNoOrTags : [];
            $lyricists = is_array($tagsOrLyricists) ? $tagsOrLyricists : [];
            $composers = is_array($lyricistsOrComposers) ? $lyricistsOrComposers : [];
            $arrangerItems = is_array($composersOrArrangers) ? $composersOrArrangers : [];
        } else {
            $lyricsLink = is_string($lyricsLinkOrType) ? $lyricsLinkOrType : null;
            $type = $typeOrIsDisplay;
            $isDisplay = is_bool($isDisplayOrOrderNo) ? $isDisplayOrOrderNo : true;
            $orderNo = is_int($orderNoOrTags) ? $orderNoOrTags : 1;
            $tags = is_array($tagsOrLyricists) ? $tagsOrLyricists : [];
            $lyricists = is_array($lyricistsOrComposers) ? $lyricistsOrComposers : [];
            $composers = is_array($composersOrArrangers) ? $composersOrArrangers : [];
            $arrangerItems = is_array($arrangers) ? $arrangers : [];
        }

        assert($type instanceof SongType);

        return new Song(
            SongId::reconstruct($songId),
            Title::reconstruct($title),
            Description::reconstruct($description),
            is_null($lyricsLink) ? null : LyricsLink::reconstruct($lyricsLink),
            $type,
            $isDisplay,
            OrderNo::reconstruct($orderNo),
            SongTagReferences::fromArray($tags)->unwrap(),
            Lyricists::fromArray($lyricists)->unwrap(),
            Composers::fromArray($composers)->unwrap(),
            Arrangers::fromArray($arrangerItems)->unwrap(),
        );
    }

    protected function createSongTag(string $songTagId, string $name, int $orderNo): SongTag
    {
        return new SongTag(
            SongTagId::reconstruct($songTagId),
            SongTagName::reconstruct($name),
            OrderNo::reconstruct($orderNo),
        );
    }

    protected function createAdminUser(
        string $adminUserId,
        string $email,
        Role $role = Role::General,
        array $permissions = [],
        ?DateTimeImmutable $createdAt = null,
        string $name = 'テストユーザー',
    ): AdminUser {
        return AdminUser::reconstruct(
            $adminUserId,
            $name,
            $email,
            $createdAt ?? new DateTimeImmutable(),
            $role->value,
            $permissions,
        );
    }

    protected function createAccessToken(string $jwt): AccessToken
    {
        return new AccessToken(Jwt::reconstruct($jwt));
    }

    protected function createRefreshToken(
        string $refreshTokenId,
        string $adminUserId,
        string $tokenValue,
        DateTimeImmutable $expiredAt,
        ConsumptionStatus $status,
    ): RefreshToken {
        return new RefreshToken(
            RefreshTokenId::reconstruct($refreshTokenId),
            AdminUserId::reconstruct($adminUserId),
            HashedTokenValue::reconstruct($tokenValue),
            ExpiredAt::reconstruct($expiredAt),
            $status,
        );
    }
}
