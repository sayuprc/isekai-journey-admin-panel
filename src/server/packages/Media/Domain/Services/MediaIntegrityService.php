<?php

declare(strict_types=1);

namespace Media\Domain\Services;

use Media\Domain\Models\Media;
use Media\Domain\Models\MediaFormat;
use Media\Domain\Models\MediaId;
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
    ) {
    }

    /**
     * @return Result<Media, DomainError>
     */
    public function prepareForCreate(
        string $title,
        string $url,
        int $typeValue,
        int $formatValue,
        bool $isDisplay,
    ): Result {
        return Result::collect6(
            MediaId::create($this->generator->generate()),
            MediaTitle::create($title),
            MediaUrl::create($url),
            $this->toMediaType($typeValue),
            $this->toMediaFormat($formatValue),
            new Ok($isDisplay),
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
            ->map(fn (array $values): Media => new Media(...$values));
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
