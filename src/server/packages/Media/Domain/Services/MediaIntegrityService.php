<?php

declare(strict_types=1);

namespace Media\Domain\Services;

use DateMalformedStringException;
use DateType\ImmutableDate;
use Media\Domain\Models\Media;
use Media\Domain\Models\MediaId;
use Media\Domain\Models\MediaPublishedAt;
use Media\Domain\Models\MediaRepositoryInterface;
use Media\Domain\Models\MediaTitle;
use Media\Domain\Models\MediaType;
use Media\Domain\Models\MediaUrl;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Contracts\Uuid\UuidGeneratorInterface;
use Support\Domain\Error\DomainError;
use Support\Domain\Error\DomainValidationError;
use Support\Domain\Error\EntityRuleViolationError;

class MediaIntegrityService
{
    public function __construct(
        private readonly UuidGeneratorInterface $generator,
        private readonly MediaRepositoryInterface $repository,
    ) {
    }

    /**
     * @return Result<Media, DomainError>
     */
    public function prepareForCreate(
        string $title,
        string $url,
        string $publishedAt,
        int $typeValue,
        bool $isDisplay,
    ): Result {
        $result = $this->build(
            $this->generator->generate(),
            $title,
            $url,
            $publishedAt,
            $typeValue,
            $isDisplay,
        );

        if ($result->isErr()) {
            return new Err($result->unwrapErr());
        }

        $media = $result->unwrap();

        if (! is_null($this->repository->findByUrl($media->url))) {
            return new Err(new DomainValidationError(['url' => ['同じURLのメディアが既に存在します']]));
        }

        return new Ok($media);
    }

    /**
     * @return Result<Media, DomainError>
     */
    public function prepareForUpdate(
        string $mediaId,
        string $title,
        string $url,
        string $publishedAt,
        int $typeValue,
        bool $isDisplay,
    ): Result {
        $result = $this->build(
            $mediaId,
            $title,
            $url,
            $publishedAt,
            $typeValue,
            $isDisplay,
        );

        if ($result->isErr()) {
            return new Err($result->unwrapErr());
        }

        $media = $result->unwrap();
        $found = $this->repository->findByUrl($media->url);

        if (! is_null($found) && ! $found->equals($media)) {
            return new Err(new DomainValidationError(['url' => ['同じURLのメディアが既に存在します']]));
        }

        return new Ok($media);
    }

    /**
     * @return Result<Media, DomainError>
     */
    private function build(
        string $mediaId,
        string $title,
        string $url,
        string $publishedAt,
        int $typeValue,
        bool $isDisplay,
    ): Result {
        return Result::collect6(
            MediaId::create($mediaId),
            MediaTitle::create($title),
            MediaUrl::create($url),
            $this->toPublishedAt($publishedAt),
            $this->toMediaType($typeValue),
            new Ok($isDisplay),
        )
            ->mapErr(function (array $errors): DomainError {
                $messages = [];
                foreach ($errors as $error) {
                    if ($error instanceof EntityRuleViolationError) {
                        $messages[$error->field] ??= [];
                        $messages[$error->field][] = $error->message;
                    }
                }

                return new DomainValidationError($messages);
            })
            ->map(fn (array $values): Media => new Media(...$values));
    }

    /**
     * @return Result<MediaPublishedAt, DomainError>
     */
    private function toPublishedAt(string $publishedAt): Result
    {
        $normalized = trim($publishedAt);

        if ($normalized === '') {
            return new Err(new EntityRuleViolationError('publishedAt', '公開日は必須です'));
        }

        try {
            return MediaPublishedAt::create(new ImmutableDate($normalized));
        } catch (DateMalformedStringException) {
            return new Err(new EntityRuleViolationError('publishedAt', '公開日が不正です'));
        }
    }

    /**
     * 未知・不正な種別値は投入を止めず MediaType::Other に倒す。
     *
     * @return Result<MediaType, DomainError>
     */
    private function toMediaType(int $typeValue): Result
    {
        return new Ok(MediaType::tryFrom($typeValue) ?? MediaType::Other);
    }
}
