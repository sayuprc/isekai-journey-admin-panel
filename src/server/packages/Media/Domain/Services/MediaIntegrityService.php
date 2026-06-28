<?php

declare(strict_types=1);

namespace Media\Domain\Services;

use DateMalformedStringException;
use DateType\ImmutableDate;
use Media\Domain\Models\Media;
use Media\Domain\Models\MediaFormat;
use Media\Domain\Models\MediaId;
use Media\Domain\Models\MediaPlatform;
use Media\Domain\Models\MediaPublishedAt;
use Media\Domain\Models\MediaRepositoryInterface;
use Media\Domain\Models\MediaThumbnail;
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
        int $formatValue,
        bool $isDisplay,
        ?int $platformValue = null,
    ): Result {
        $result = $this->build(
            $this->generator->generate(),
            $title,
            $url,
            $publishedAt,
            $typeValue,
            $formatValue,
            $isDisplay,
            $platformValue,
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
        int $formatValue,
        bool $isDisplay,
        ?int $platformValue = null,
    ): Result {
        $result = $this->build(
            $mediaId,
            $title,
            $url,
            $publishedAt,
            $typeValue,
            $formatValue,
            $isDisplay,
            $platformValue,
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
        int $formatValue,
        bool $isDisplay,
        ?int $platformValue,
    ): Result {
        return Result::collect7(
            MediaId::create($mediaId),
            MediaTitle::create($title),
            MediaUrl::create($url),
            $this->toPublishedAt($publishedAt),
            $this->toMediaType($typeValue),
            $this->toMediaFormat($formatValue),
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
            ->andThen(fn (array $values): Result => $this->toMedia($values, $platformValue));
    }

    /**
     * @param array{MediaId, MediaTitle, MediaUrl, MediaPublishedAt, MediaType, MediaFormat, bool} $values
     *
     * @return Result<Media, DomainError>
     */
    private function toMedia(array $values, ?int $platformValue): Result
    {
        [$mediaId, $title, $url, $publishedAt, $type, $format, $isDisplay] = $values;

        return $this->toPlatform($platformValue, $url)
            ->andThen(fn (MediaPlatform $platform): Result => match ($platform) {
                MediaPlatform::YouTube => MediaThumbnail::fromYouTubeUrl($url)
                    ->map(fn (MediaThumbnail $thumbnail): Media => Media::youtube(
                        $mediaId,
                        $title,
                        $url,
                        $publishedAt,
                        $type,
                        $format,
                        $isDisplay,
                        $thumbnail,
                    )),
                MediaPlatform::X => new Ok(Media::x($mediaId, $title, $url, $publishedAt, $type, $format, $isDisplay)),
                MediaPlatform::Other => new Ok(Media::other($mediaId, $title, $url, $publishedAt, $type, $format, $isDisplay)),
            });
    }

    /**
     * @return Result<MediaPlatform, DomainError>
     */
    private function toPlatform(?int $platformValue, MediaUrl $url): Result
    {
        if (is_null($platformValue)) {
            return new Ok(MediaPlatform::fromUrl($url));
        }

        $platform = MediaPlatform::tryFrom($platformValue);

        if (is_null($platform)) {
            return new Err(new EntityRuleViolationError('platformValue', "不正なプラットフォームです: {$platformValue}"));
        }

        return new Ok($platform);
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
     * @return Result<MediaType, DomainError>
     */
    private function toMediaType(int $typeValue): Result
    {
        $type = MediaType::tryFrom($typeValue);

        if (is_null($type)) {
            return new Err(new EntityRuleViolationError(MediaType::class, "不正なメディア種別です: {$typeValue}"));
        }

        return new Ok($type);
    }

    /**
     * @return Result<MediaFormat, DomainError>
     */
    private function toMediaFormat(int $formatValue): Result
    {
        $format = MediaFormat::tryFrom($formatValue);

        if (is_null($format)) {
            return new Err(new EntityRuleViolationError(MediaFormat::class, "不正なメディア形式です: {$formatValue}"));
        }

        return new Ok($format);
    }
}
