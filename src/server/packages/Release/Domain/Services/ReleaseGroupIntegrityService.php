<?php

declare(strict_types=1);

namespace Release\Domain\Services;

use Release\Domain\Models\Description;
use Release\Domain\Models\ReleaseGroup;
use Release\Domain\Models\ReleaseGroupId;
use Release\Domain\Models\ReleaseGroupTitle;
use Release\Domain\Models\ReleaseGroupType;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\Contracts\Uuid\UuidGeneratorInterface;
use Support\Domain\Error\DomainError;
use Support\Domain\Error\DomainValidationError;
use Support\Domain\Error\EntityRuleViolationError;

class ReleaseGroupIntegrityService
{
    public function __construct(private readonly UuidGeneratorInterface $generator)
    {
    }

    /**
     * @return Result<ReleaseGroup, DomainError>
     */
    public function prepareForCreate(
        string $title,
        int $typeValue,
        string $description,
        bool $isDisplay,
    ): Result {
        return $this->build($this->generator->generate(), $title, $typeValue, $description, $isDisplay);
    }

    /**
     * @return Result<ReleaseGroup, DomainError>
     */
    public function prepareForUpdate(
        string $releaseGroupId,
        string $title,
        int $typeValue,
        string $description,
        bool $isDisplay,
    ): Result {
        return $this->build($releaseGroupId, $title, $typeValue, $description, $isDisplay);
    }

    /**
     * @return Result<ReleaseGroup, DomainError>
     */
    private function build(
        string $releaseGroupId,
        string $title,
        int $typeValue,
        string $description,
        bool $isDisplay,
    ): Result {
        return Result::collect4(
            ReleaseGroupId::create($releaseGroupId),
            ReleaseGroupTitle::create($title),
            $this->toReleaseGroupType($typeValue),
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
            ->map(fn (array $values): ReleaseGroup => new ReleaseGroup(...[...$values, $isDisplay]));
    }

    /**
     * @return Result<ReleaseGroupType, DomainError>
     */
    private function toReleaseGroupType(int $typeValue): Result
    {
        $type = ReleaseGroupType::tryFrom($typeValue);

        if (is_null($type)) {
            return new Err(new EntityRuleViolationError('typeValue', "不正なリリースグループ種別です: {$typeValue}"));
        }

        return new Ok($type);
    }
}
