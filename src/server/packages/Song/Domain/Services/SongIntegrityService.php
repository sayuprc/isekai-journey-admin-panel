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
use Song\Domain\Models\SongAttribute;
use Song\Domain\Models\SongFactoryInterface;
use Song\Domain\Models\SongId;
use Song\Domain\Models\SongRepositoryInterface;
use Song\Domain\Models\Title;
use SongType\Domain\Models\SongType;
use Support\Contracts\UuidGeneratorInterface;
use Support\Domain\Error\BusinessRuleViolationError;
use Support\Domain\Error\DomainError;
use Support\Domain\Error\DomainValidationError;
use Support\Domain\Error\EntityRuleViolationError;
use Support\Domain\ValueObjects\OrderNo;

/**
 * @phpstan-type creator array{creatorId: string}
 */
class SongIntegrityService
{
    public function __construct(
        private readonly UuidGeneratorInterface $generator,
        private readonly SongFactoryInterface $factory,
        private readonly SongRepositoryInterface $songRepository,
        private readonly CreatorRepositoryInterface $creatorRepository,
    ) {
    }

    /**
     * @param list<creator> $lyricists
     * @param list<creator> $composers
     * @param list<creator> $arrangers
     *
     * @return Result<Song, DomainError>
     */
    public function prepareForCreate(
        string $title,
        string $description,
        int $songType,
        ?int $attribute,
        array $lyricists,
        array $composers,
        array $arrangers,
    ): Result {
        $result = Result::collect3(
            Lyricists::fromArray($lyricists),
            Composers::fromArray($composers),
            Arrangers::fromArray($arrangers),
        );

        if ($result->isErr()) {
            return new Err($this->mergeValidationErrors($result->unwrapErr()));
        }

        $creators = $result->unwrap();

        if (! $this->existsCreators(...$creators)) {
            return new Err(new BusinessRuleViolationError('指定されたクリエイターの一部が存在しません。'));
        }

        return $this->build(
            $this->generator->generate(),
            $title,
            $description,
            $songType,
            $attribute,
            // 更新時に同じ値になることを防ぐために +10 で採番
            $this->songRepository->getMaxOrderNo() + 10,
            ...$creators,
        );
    }

    /**
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
        int $songType,
        ?int $attribute,
        int $orderNo,
        array $lyricists,
        array $composers,
        array $arrangers,
    ): Result {
        $result = Result::collect3(
            Lyricists::fromArray($lyricists),
            Composers::fromArray($composers),
            Arrangers::fromArray($arrangers),
        );

        if ($result->isErr()) {
            return new Err($this->mergeValidationErrors($result->unwrapErr()));
        }

        $creators = $result->unwrap();

        if (! $this->existsCreators(...$creators)) {
            return new Err(new BusinessRuleViolationError('指定されたクリエイターの一部が存在しません。'));
        }

        return $this->build(
            $songId,
            $title,
            $description,
            $songType,
            $attribute,
            $orderNo,
            ...$creators,
        );
    }

    /**
     * @return Result<Song, DomainError>
     */
    private function build(
        string $songId,
        string $title,
        string $description,
        int $songType,
        ?int $attribute,
        int $orderNo,
        Lyricists $lyricists,
        Composers $composers,
        Arrangers $arrangers,
    ): Result {
        return Result::collect6(
            SongId::create($songId),
            Title::create($title),
            Description::create($description),
            $this->toSongType($songType),
            $this->toSongAttribute($attribute),
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
            ->map(fn (array $values): Song => $this->factory->create(...[...$values, $lyricists, $composers, $arrangers]));
    }

    /**
     * @return Result<SongType, DomainError>
     */
    private function toSongType(int $songType): Result
    {
        $result = SongType::tryFrom($songType);

        if (is_null($result)) {
            return new Err(new EntityRuleViolationError(SongType::class, "不正な楽曲種別です: {$songType}"));
        }

        return new Ok($result);
    }

    /**
     * @return Result<SongAttribute|null, DomainError>
     */
    private function toSongAttribute(?int $attribute): Result
    {
        if (is_null($attribute)) {
            return new Ok(null);
        }

        $result = SongAttribute::tryFrom($attribute);

        if (is_null($result)) {
            return new Err(new EntityRuleViolationError(SongAttribute::class, "不正な楽曲属性です: {$attribute}"));
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
}
