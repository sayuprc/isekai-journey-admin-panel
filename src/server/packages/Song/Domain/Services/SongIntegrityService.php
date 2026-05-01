<?php

declare(strict_types=1);

namespace Song\Domain\Services;

use Creator\Domain\Models\CreatorId;
use Creator\Domain\Models\CreatorRepositoryInterface;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Song\Domain\Models\Creators\Arrangers;
use Song\Domain\Models\Creators\Composers;
use Song\Domain\Models\Creators\Lyricists;
use Song\Domain\Models\Description;
use Song\Domain\Models\Song;
use Song\Domain\Models\SongFactoryInterface;
use Song\Domain\Models\SongId;
use Song\Domain\Models\SongRepositoryInterface;
use Song\Domain\Models\SongType;
use Song\Domain\Models\Tag\SongTagRepositoryInterface;
use Song\Domain\Models\Tags\SongTagReferences;
use Song\Domain\Models\Title;
use Support\Contracts\Uuid\UuidGeneratorInterface;
use Support\Domain\Error\BusinessRuleViolationError;
use Support\Domain\Error\DomainError;
use Support\Domain\Error\DomainValidationError;
use Support\Domain\Error\EntityRuleViolationError;
use Support\Domain\ValueObjects\OrderNo;

/**
 * @phpstan-type creator array{creatorId: string}
 * @phpstan-type songTag array{songTagId: string}
 */
class SongIntegrityService
{
    public function __construct(
        private readonly UuidGeneratorInterface $generator,
        private readonly SongFactoryInterface $factory,
        private readonly SongRepositoryInterface $songRepository,
        private readonly CreatorRepositoryInterface $creatorRepository,
        private readonly SongTagRepositoryInterface $songTagRepository,
    ) {
    }

    /**
     * @param list<songTag> $tags
     * @param list<creator> $lyricists
     * @param list<creator> $composers
     * @param list<creator> $arrangers
     *
     * @return Result<Song, DomainError>
     */
    public function prepareForCreate(
        string $title,
        string $description,
        int $type,
        bool $isDisplay,
        array $tags,
        array $lyricists,
        array $composers,
        array $arrangers,
    ): Result {
        $result = Result::collect4(
            Lyricists::fromArray($lyricists),
            Composers::fromArray($composers),
            Arrangers::fromArray($arrangers),
            SongTagReferences::fromArray($tags),
        );

        if ($result->isErr()) {
            return new Err($this->mergeValidationErrors($result->unwrapErr()));
        }

        [$lyricists, $composers, $arrangers, $tags] = $result->unwrap();

        if (! $this->existsCreators($lyricists, $composers, $arrangers)) {
            return new Err(new BusinessRuleViolationError('指定されたクリエイターの一部が存在しません。'));
        }

        if (! $this->existsSongTags($tags)) {
            return new Err(new BusinessRuleViolationError('指定された楽曲タグの一部が存在しません。'));
        }

        return $this->build(
            $this->generator->generate(),
            $title,
            $description,
            $type,
            $isDisplay,
            // 更新時に同じ値になることを防ぐために +10 で採番
            $this->songRepository->getMaxOrderNo() + 10,
            $lyricists,
            $composers,
            $arrangers,
            $tags,
        );
    }

    /**
     * @param list<songTag> $tags
     * @param list<creator> $lyricists
     * @param list<creator> $composers
     * @param list<creator> $arrangers
     *
     * @return Result<Song, DomainError>
     */
    public function prepareForUpdate(
        string $songId,
        string $title,
        string $description,
        int $type,
        bool $isDisplay,
        int $orderNo,
        array $tags,
        array $lyricists,
        array $composers,
        array $arrangers,
    ): Result {
        $result = Result::collect4(
            Lyricists::fromArray($lyricists),
            Composers::fromArray($composers),
            Arrangers::fromArray($arrangers),
            SongTagReferences::fromArray($tags),
        );

        if ($result->isErr()) {
            return new Err($this->mergeValidationErrors($result->unwrapErr()));
        }

        [$lyricists, $composers, $arrangers, $tags] = $result->unwrap();

        if (! $this->existsCreators($lyricists, $composers, $arrangers)) {
            return new Err(new BusinessRuleViolationError('指定されたクリエイターの一部が存在しません。'));
        }

        if (! $this->existsSongTags($tags)) {
            return new Err(new BusinessRuleViolationError('指定された楽曲タグの一部が存在しません。'));
        }

        return $this->build(
            $songId,
            $title,
            $description,
            $type,
            $isDisplay,
            $orderNo,
            $lyricists,
            $composers,
            $arrangers,
            $tags,
        );
    }

    /**
     * @return Result<Song, DomainError>
     */
    private function build(
        string $songId,
        string $title,
        string $description,
        int $type,
        bool $isDisplay,
        int $orderNo,
        Lyricists $lyricists,
        Composers $composers,
        Arrangers $arrangers,
        SongTagReferences $tags,
    ): Result {
        return Result::collect6(
            SongId::create($songId),
            Title::create($title),
            Description::create($description),
            $this->toSongType($type),
            new Ok($isDisplay),
            OrderNo::create($orderNo),
        )
            ->mapErr(function (array $errors): DomainValidationError {
                $messages = [];
                foreach ($errors as $error) {
                    if ($error instanceof EntityRuleViolationError) {
                        $messages[$error->field] ??= [];
                        $messages[$error->field][] = $error->message;
                    }
                }

                return new DomainValidationError($messages);
            })
            ->map(fn (array $values): Song => $this->factory->create(...[...$values, $tags, $lyricists, $composers, $arrangers]));
    }

    /**
     * @return Result<SongType, DomainError>
     */
    private function toSongType(int $type): Result
    {
        $result = SongType::tryFrom($type);

        if (is_null($result)) {
            return new Err(new EntityRuleViolationError(SongType::class, "不正な楽曲種別です: {$type}"));
        }

        return new Ok($result);
    }

    /**
     * @param array<int, DomainValidationError|null> $errors
     */
    private function mergeValidationErrors(array $errors): DomainValidationError
    {
        $messages = [];

        foreach ($errors as $error) {
            if (! $error instanceof DomainValidationError) {
                continue;
            }

            foreach ($error->errors as $field => $fieldMessages) {
                $messages[$field] ??= [];
                $messages[$field] = [...$messages[$field], ...$fieldMessages];
            }
        }

        return new DomainValidationError($messages);
    }

    private function existsCreators(Lyricists $lyricists, Composers $composers, Arrangers $arrangers): bool
    {
        $creatorIds = [];

        foreach ([$lyricists, $composers, $arrangers] as $items) {
            foreach ($items as $item) {
                if (! isset($creatorIds[$item->creatorId->value])) {
                    $creatorIds[$item->creatorId->value] = $item->creatorId;
                }
            }
        }

        $founds = $this->creatorRepository->findByIds(...array_values($creatorIds));

        return count($creatorIds) === count($founds);
    }

    private function existsSongTags(SongTagReferences $tags): bool
    {
        $songTagIds = [];

        foreach ($tags as $tag) {
            if (! isset($songTagIds[$tag->songTagId->value])) {
                $songTagIds[$tag->songTagId->value] = $tag->songTagId;
            }
        }

        if ($songTagIds === []) {
            return true;
        }

        $founds = $this->songTagRepository->findByIds(...array_values($songTagIds));

        return count($songTagIds) === count($founds);
    }
}
