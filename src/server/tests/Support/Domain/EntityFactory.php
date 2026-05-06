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
use DateTimeImmutable;
use Media\Domain\Models\Media;
use Media\Domain\Models\MediaFormat;
use Media\Domain\Models\MediaId;
use Media\Domain\Models\MediaPublishedAt;
use Media\Domain\Models\MediaTitle;
use Media\Domain\Models\MediaType;
use Media\Domain\Models\MediaUrl;
use Person\Domain\Models\Person;
use Person\Domain\Models\PersonId;
use Person\Domain\Models\PersonName;
use Song\Domain\Models\Description;
use Song\Domain\Models\LyricsLink;
use Song\Domain\Models\Media\SongMediaLinks;
use Song\Domain\Models\Persons\SongPersonRole;
use Song\Domain\Models\Persons\SongPersons;
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
    protected function createPerson(string $personId, string $name, int $orderNo): Person
    {
        return new Person(
            PersonId::reconstruct($personId),
            PersonName::reconstruct($name),
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
        mixed $tagsOrPersons = [],
        mixed $personsOrLyricists = [],
        mixed $lyricistsOrComposers = [],
        mixed $composersOrArrangers = [],
        mixed $arrangers = [],
        mixed $media = [],
    ): Song {
        if ($lyricsLinkOrType instanceof SongType) {
            $lyricsLink = null;
            $type = $lyricsLinkOrType;
            $isDisplay = is_bool($typeOrIsDisplay) ? $typeOrIsDisplay : true;
            $orderNo = is_int($isDisplayOrOrderNo) ? $isDisplayOrOrderNo : 1;
            $tags = is_array($orderNoOrTags) ? $orderNoOrTags : [];
            $persons = $this->normalizeSongPersons(
                is_array($tagsOrPersons) ? $tagsOrPersons : [],
                is_array($personsOrLyricists) ? $personsOrLyricists : [],
                is_array($lyricistsOrComposers) ? $lyricistsOrComposers : [],
                is_array($composersOrArrangers) ? $composersOrArrangers : [],
            );
            $media = $this->normalizeSongMedia(
                is_array($lyricistsOrComposers) ? $lyricistsOrComposers : [],
                is_array($composersOrArrangers) ? $composersOrArrangers : [],
                is_array($arrangers) ? $arrangers : [],
                is_array($media) ? $media : [],
            );
        } else {
            $lyricsLink = is_string($lyricsLinkOrType) ? $lyricsLinkOrType : null;
            $type = $typeOrIsDisplay;
            $isDisplay = is_bool($isDisplayOrOrderNo) ? $isDisplayOrOrderNo : true;
            $orderNo = is_int($orderNoOrTags) ? $orderNoOrTags : 1;
            $tags = is_array($tagsOrPersons) ? $tagsOrPersons : [];
            $persons = $this->normalizeSongPersons(
                is_array($personsOrLyricists) ? $personsOrLyricists : [],
                is_array($lyricistsOrComposers) ? $lyricistsOrComposers : [],
                is_array($composersOrArrangers) ? $composersOrArrangers : [],
                is_array($arrangers) ? $arrangers : [],
            );
            $media = $this->normalizeSongMedia(
                is_array($lyricistsOrComposers) ? $lyricistsOrComposers : [],
                is_array($composersOrArrangers) ? $composersOrArrangers : [],
                is_array($arrangers) ? $arrangers : [],
                is_array($media) ? $media : [],
            );
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
            SongPersons::fromArray($persons)->unwrap(),
            SongMediaLinks::fromArray(is_array($media) ? $media : [])->unwrap(),
        );
    }

    /**
     * @param list<array<string, mixed>> $personsOrLyricists
     * @param list<array<string, mixed>> $composersOrLegacyLyricists
     * @param list<array<string, mixed>> $arrangersOrLegacyComposers
     * @param list<array<string, mixed>> $legacyArrangers
     *
     * @return list<array{personId: string, role: int, orderNo: int}>
     */
    private function normalizeSongPersons(
        array $personsOrLyricists,
        array $composersOrLegacyLyricists,
        array $arrangersOrLegacyComposers,
        array $legacyArrangers,
    ): array {
        if ($personsOrLyricists === [] || array_key_exists('personId', $personsOrLyricists[0] ?? [])) {
            /** @var list<array{personId: string, role: int, orderNo: int}> */
            return $personsOrLyricists;
        }

        $toPerson = fn (array $item, SongPersonRole $role): array => [
            'personId' => (string)$item['personId'],
            'role' => $role->value,
            'orderNo' => (int)$item['orderNo'],
        ];

        return [
            ...array_map(fn (array $item): array => $toPerson($item, SongPersonRole::Lyricist), $personsOrLyricists),
            ...array_map(fn (array $item): array => $toPerson($item, SongPersonRole::Composer), $composersOrLegacyLyricists),
            ...array_map(fn (array $item): array => $toPerson($item, SongPersonRole::Arranger), $arrangersOrLegacyComposers),
            ...array_map(fn (array $item): array => $toPerson($item, SongPersonRole::Arranger), $legacyArrangers),
        ];
    }

    /**
     * @param list<array<string, mixed>> ...$candidates
     *
     * @return list<array{mediaId: string, orderNo: int}>
     */
    private function normalizeSongMedia(array ...$candidates): array
    {
        foreach ($candidates as $candidate) {
            if ($candidate !== [] && array_key_exists('mediaId', $candidate[0] ?? [])) {
                /** @var list<array{mediaId: string, orderNo: int}> */
                return $candidate;
            }
        }

        return [];
    }

    protected function createSongTag(string $songTagId, string $name, int $orderNo): SongTag
    {
        return new SongTag(
            SongTagId::reconstruct($songTagId),
            SongTagName::reconstruct($name),
            OrderNo::reconstruct($orderNo),
        );
    }

    protected function createMedia(
        string $mediaId,
        string $title,
        string $url,
        MediaType $type,
        bool $isDisplay,
        MediaFormat $format = MediaFormat::Other,
        ?\DateType\ImmutableDate $publishedAt = null,
    ): Media {
        return new Media(
            MediaId::reconstruct($mediaId),
            MediaTitle::reconstruct($title),
            MediaUrl::reconstruct($url),
            MediaPublishedAt::reconstruct($publishedAt ?? new \DateType\ImmutableDate('2024-01-01')),
            $type,
            $format,
            $isDisplay,
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
