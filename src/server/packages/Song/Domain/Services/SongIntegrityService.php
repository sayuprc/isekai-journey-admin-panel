<?php

declare(strict_types=1);

namespace Song\Domain\Services;

use Person\Domain\Models\PersonId;
use Person\Domain\Models\PersonRepositoryInterface;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Song\Domain\Models\Description;
use Song\Domain\Models\LyricsLink;
use Song\Domain\Models\Persons\SongPersons;
use Song\Domain\Models\Song;
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
 * @phpstan-type person array{personId: string, role: string, orderNo: int}
 * @phpstan-type songTag array{songTagId: string}
 */
class SongIntegrityService
{
    public function __construct(
        private readonly UuidGeneratorInterface $generator,
        private readonly SongRepositoryInterface $songRepository,
        private readonly PersonRepositoryInterface $personRepository,
        private readonly SongTagRepositoryInterface $songTagRepository,
    ) {
    }

    /**
     * @param list<songTag> $tags
     * @param list<person>  $persons
     *
     * @return Result<Song, DomainError>
     */
    public function prepareForCreate(
        string $title,
        string $description,
        ?string $lyricsLink,
        int $type,
        bool $isDisplay,
        array $tags,
        array $persons,
    ): Result {
        $result = Result::collect(
            SongPersons::fromArray($persons),
            SongTagReferences::fromArray($tags),
        );

        if ($result->isErr()) {
            return new Err($this->mergeValidationErrors($result->unwrapErr()));
        }

        [$persons, $tags] = $result->unwrap();

        if (! $this->existsPersons($persons)) {
            return new Err(new BusinessRuleViolationError('指定された人物の一部が存在しません。'));
        }

        if (! $this->existsSongTags($tags)) {
            return new Err(new BusinessRuleViolationError('指定された楽曲タグの一部が存在しません。'));
        }

        return $this->build(
            $this->generator->generate(),
            $title,
            $description,
            $lyricsLink,
            $type,
            $isDisplay,
            // 更新時に同じ値になることを防ぐために +10 で採番
            $this->songRepository->getMaxOrderNo() + 10,
            $persons,
            $tags,
        );
    }

    /**
     * @param list<songTag> $tags
     * @param list<person>  $persons
     *
     * @return Result<Song, DomainError>
     */
    public function prepareForUpdate(
        string $songId,
        string $title,
        string $description,
        ?string $lyricsLink,
        int $type,
        bool $isDisplay,
        int $orderNo,
        array $tags,
        array $persons,
    ): Result {
        $result = Result::collect(
            SongPersons::fromArray($persons),
            SongTagReferences::fromArray($tags),
        );

        if ($result->isErr()) {
            return new Err($this->mergeValidationErrors($result->unwrapErr()));
        }

        [$persons, $tags] = $result->unwrap();

        if (! $this->existsPersons($persons)) {
            return new Err(new BusinessRuleViolationError('指定された人物の一部が存在しません。'));
        }

        if (! $this->existsSongTags($tags)) {
            return new Err(new BusinessRuleViolationError('指定された楽曲タグの一部が存在しません。'));
        }

        return $this->build(
            $songId,
            $title,
            $description,
            $lyricsLink,
            $type,
            $isDisplay,
            $orderNo,
            $persons,
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
        ?string $lyricsLink,
        int $type,
        bool $isDisplay,
        int $orderNo,
        SongPersons $persons,
        SongTagReferences $tags,
    ): Result {
        $normalizedLyricsLink = $this->normalizeOptionalString($lyricsLink);
        $lyricsLinkResult = is_null($normalizedLyricsLink)
            ? new Ok(null)
            : LyricsLink::create($normalizedLyricsLink);

        return Result::collect7(
            SongId::create($songId),
            Title::create($title),
            Description::create($description),
            $lyricsLinkResult,
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
            ->map(fn (array $values): Song => new Song(...[...$values, $tags, $persons]));
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

    private function existsPersons(SongPersons $persons): bool
    {
        $personIds = [];

        foreach ($persons as $item) {
            if (! isset($personIds[$item->personId->value])) {
                $personIds[$item->personId->value] = $item->personId;
            }
        }

        $founds = $this->personRepository->findByIds(...array_values($personIds));

        return count($personIds) === count($founds);
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

    private function normalizeOptionalString(?string $value): ?string
    {
        if (is_null($value)) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }
}
