<?php

declare(strict_types=1);

namespace Release\Domain\Services;

use DateMalformedStringException;
use DateType\ImmutableDate;
use Release\Domain\Models\Description;
use Release\Domain\Models\Release;
use Release\Domain\Models\ReleaseDistributionType;
use Release\Domain\Models\ReleasedOn;
use Release\Domain\Models\ReleaseId;
use Release\Domain\Models\ReleaseTitle;
use Release\Domain\Models\ReleaseType;
use Release\Domain\Models\TrackEntries;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Song\Domain\Models\SongRepositoryInterface;
use Support\Contracts\Uuid\UuidGeneratorInterface;
use Support\Domain\Error\BusinessRuleViolationError;
use Support\Domain\Error\DomainError;
use Support\Domain\Error\DomainValidationError;
use Support\Domain\Error\EntityRuleViolationError;

class ReleaseIntegrityService
{
    public function __construct(
        private readonly UuidGeneratorInterface $generator,
        private readonly SongRepositoryInterface $songRepository,
    ) {
    }

    /**
     * @return Result<Release, DomainError>
     */
    public function prepareForCreate(
        string $title,
        int $typeValue,
        int $distributionTypeValue,
        string $releasedOn,
        string $description,
        bool $isDisplay,
    ): Result {
        return $this->build(
            $this->generator->generate(),
            $title,
            $typeValue,
            $distributionTypeValue,
            $releasedOn,
            $description,
            $isDisplay,
            [],
        );
    }

    /**
     * @param list<array{songId: string, trackNo: int}> $trackEntries
     *
     * @return Result<Release, DomainError>
     */
    public function prepareForUpdate(
        string $releaseId,
        string $title,
        int $typeValue,
        int $distributionTypeValue,
        string $releasedOn,
        string $description,
        bool $isDisplay,
        array $trackEntries,
    ): Result {
        $result = $this->build(
            $releaseId,
            $title,
            $typeValue,
            $distributionTypeValue,
            $releasedOn,
            $description,
            $isDisplay,
            $trackEntries,
        );

        if ($result->isErr()) {
            return new Err($result->unwrapErr());
        }

        $release = $result->unwrap();

        if (! $this->existsSongs($release->trackEntries)) {
            return new Err(new BusinessRuleViolationError('指定された楽曲の一部が存在しません。'));
        }

        return new Ok($release);
    }

    /**
     * @param list<array{songId: string, trackNo: int}> $trackEntries
     *
     * @return Result<Release, DomainError>
     */
    private function build(
        string $releaseId,
        string $title,
        int $typeValue,
        int $distributionTypeValue,
        string $releasedOn,
        string $description,
        bool $isDisplay,
        array $trackEntries,
    ): Result {
        $trackEntriesResult = TrackEntries::fromArray($trackEntries);

        if ($trackEntriesResult->isErr()) {
            return new Err($trackEntriesResult->unwrapErr());
        }

        return Result::collect6(
            ReleaseId::create($releaseId),
            ReleaseTitle::create($title),
            $this->toReleaseType($typeValue),
            $this->toReleaseDistributionType($distributionTypeValue),
            $this->toReleasedOn($releasedOn),
            Description::create($description),
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
            ->map(fn (array $values): Release => new Release(...[...$values, $isDisplay, $trackEntriesResult->unwrap()]));
    }

    private function existsSongs(TrackEntries $trackEntries): bool
    {
        foreach ($trackEntries as $trackEntry) {
            if (is_null($this->songRepository->find($trackEntry->songId))) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return Result<ReleasedOn, DomainError>
     */
    private function toReleasedOn(string $releasedOn): Result
    {
        $normalized = trim($releasedOn);

        if ($normalized === '') {
            return new Err(new EntityRuleViolationError('releasedOn', '発売日は必須です'));
        }

        try {
            return ReleasedOn::create(new ImmutableDate($normalized));
        } catch (DateMalformedStringException) {
            return new Err(new EntityRuleViolationError('releasedOn', '発売日が不正です'));
        }
    }

    /**
     * @return Result<ReleaseType, DomainError>
     */
    private function toReleaseType(int $typeValue): Result
    {
        $type = ReleaseType::tryFrom($typeValue);

        if (is_null($type)) {
            return new Err(new EntityRuleViolationError('typeValue', "不正なリリース種別です: {$typeValue}"));
        }

        return new Ok($type);
    }

    /**
     * @return Result<ReleaseDistributionType, DomainError>
     */
    private function toReleaseDistributionType(int $distributionTypeValue): Result
    {
        $distributionType = ReleaseDistributionType::tryFrom($distributionTypeValue);

        if (is_null($distributionType)) {
            return new Err(new EntityRuleViolationError('distributionTypeValue', "不正な流通形態です: {$distributionTypeValue}"));
        }

        return new Ok($distributionType);
    }
}
