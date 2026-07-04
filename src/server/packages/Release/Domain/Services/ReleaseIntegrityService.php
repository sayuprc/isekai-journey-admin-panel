<?php

declare(strict_types=1);

namespace Release\Domain\Services;

use DateMalformedStringException;
use DateType\ImmutableDate;
use Release\Domain\Models\Description;
use Release\Domain\Models\JacketArtUrl;
use Release\Domain\Models\Media;
use Release\Domain\Models\Release;
use Release\Domain\Models\ReleasedOn;
use Release\Domain\Models\ReleaseGroupId;
use Release\Domain\Models\ReleaseGroupRepositoryInterface;
use Release\Domain\Models\ReleaseId;
use Release\Domain\Models\ReleaseName;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Song\Domain\Models\SongRepositoryInterface;
use Support\Contracts\Uuid\UuidGeneratorInterface;
use Support\Domain\Error\BusinessRuleViolationError;
use Support\Domain\Error\DomainError;
use Support\Domain\Error\DomainValidationError;
use Support\Domain\Error\EntityRuleViolationError;
use Support\Domain\ValueObjects\OrderNo;

class ReleaseIntegrityService
{
    public function __construct(
        private readonly UuidGeneratorInterface $generator,
        private readonly ReleaseGroupRepositoryInterface $releaseGroupRepository,
        private readonly SongRepositoryInterface $songRepository,
    ) {
    }

    /**
     * @param list<array{position: int, formatValue: int, tracks: list<array{songId: string, trackNo: int}>}> $media
     *
     * @return Result<Release, DomainError>
     */
    public function prepareForCreate(
        string $releaseGroupId,
        string $name,
        string $releasedOn,
        string $description,
        ?string $jacketArtUrl,
        bool $isDisplay,
        int $orderNo,
        array $media,
    ): Result {
        return $this->prepare($this->generator->generate(), $releaseGroupId, $name, $releasedOn, $description, $jacketArtUrl, $isDisplay, $orderNo, $media);
    }

    /**
     * @param list<array{position: int, formatValue: int, tracks: list<array{songId: string, trackNo: int}>}> $media
     *
     * @return Result<Release, DomainError>
     */
    public function prepareForUpdate(
        string $releaseId,
        string $releaseGroupId,
        string $name,
        string $releasedOn,
        string $description,
        ?string $jacketArtUrl,
        bool $isDisplay,
        int $orderNo,
        array $media,
    ): Result {
        return $this->prepare($releaseId, $releaseGroupId, $name, $releasedOn, $description, $jacketArtUrl, $isDisplay, $orderNo, $media);
    }

    /**
     * @param list<array{position: int, formatValue: int, tracks: list<array{songId: string, trackNo: int}>}> $media
     *
     * @return Result<Release, DomainError>
     */
    private function prepare(
        string $releaseId,
        string $releaseGroupId,
        string $name,
        string $releasedOn,
        string $description,
        ?string $jacketArtUrl,
        bool $isDisplay,
        int $orderNo,
        array $media,
    ): Result {
        $result = $this->build($releaseId, $releaseGroupId, $name, $releasedOn, $description, $jacketArtUrl, $isDisplay, $orderNo, $media);

        if ($result->isErr()) {
            return new Err($result->unwrapErr());
        }

        $release = $result->unwrap();

        if (is_null($this->releaseGroupRepository->find($release->releaseGroupId))) {
            return new Err(new BusinessRuleViolationError('指定されたリリースグループが存在しません。'));
        }

        if (! $this->existsSongs($release->media)) {
            return new Err(new BusinessRuleViolationError('指定された楽曲の一部が存在しません。'));
        }

        return new Ok($release);
    }

    /**
     * @param list<array{position: int, formatValue: int, tracks: list<array{songId: string, trackNo: int}>}> $media
     *
     * @return Result<Release, DomainError>
     */
    private function build(
        string $releaseId,
        string $releaseGroupId,
        string $name,
        string $releasedOn,
        string $description,
        ?string $jacketArtUrl,
        bool $isDisplay,
        int $orderNo,
        array $media,
    ): Result {
        $mediaResult = Media::fromArray($media);

        if ($mediaResult->isErr()) {
            return new Err($mediaResult->unwrapErr());
        }

        $normalizedJacketArtUrl = $this->normalizeOptionalString($jacketArtUrl);
        $jacketArtUrlResult = is_null($normalizedJacketArtUrl)
            ? new Ok(null)
            : JacketArtUrl::create($normalizedJacketArtUrl);

        return Result::collect7(
            ReleaseId::create($releaseId),
            ReleaseGroupId::create($releaseGroupId),
            ReleaseName::create($name),
            $this->toReleasedOn($releasedOn),
            Description::create($description),
            $jacketArtUrlResult,
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
            ->map(fn (array $values): Release => new Release(
                $values[0],
                $values[1],
                $values[2],
                $values[3],
                $values[4],
                $values[5],
                $isDisplay,
                $values[6],
                $mediaResult->unwrap(),
            ));
    }

    private function existsSongs(Media $media): bool
    {
        foreach ($media as $medium) {
            foreach ($medium->tracks as $track) {
                if (is_null($this->songRepository->find($track->songId))) {
                    return false;
                }
            }
        }

        return true;
    }

    private function normalizeOptionalString(?string $value): ?string
    {
        if (is_null($value)) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
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
}
