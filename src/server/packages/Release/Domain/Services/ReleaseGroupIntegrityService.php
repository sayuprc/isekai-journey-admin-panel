<?php

declare(strict_types=1);

namespace Release\Domain\Services;

use Release\Domain\Models\Description;
use Release\Domain\Models\ReleaseGroup;
use Release\Domain\Models\ReleaseGroupId;
use Release\Domain\Models\ReleaseGroupTitle;
use Release\Domain\Models\ReleaseGroupType;
use Support\Contracts\Uuid\UuidGeneratorInterface;
use Support\Domain\Exceptions\DomainValidationException;
use Support\Domain\Exceptions\InvalidDomainException;
use Support\Domain\Validation\FieldErrors;
use Support\Domain\ValueObjects\OrderNo;

class ReleaseGroupIntegrityService
{
    public function __construct(private readonly UuidGeneratorInterface $generator)
    {
    }

    /**
     * @throws DomainValidationException
     */
    public function prepareForCreate(
        string $title,
        int $typeValue,
        string $description,
        bool $isDisplay,
        int $orderNo,
    ): ReleaseGroup {
        return $this->build($this->generator->generate(), $title, $typeValue, $description, $isDisplay, $orderNo);
    }

    /**
     * @throws DomainValidationException
     */
    public function prepareForUpdate(
        string $releaseGroupId,
        string $title,
        int $typeValue,
        string $description,
        bool $isDisplay,
        int $orderNo,
    ): ReleaseGroup {
        return $this->build($releaseGroupId, $title, $typeValue, $description, $isDisplay, $orderNo);
    }

    private function build(
        string $releaseGroupId,
        string $title,
        int $typeValue,
        string $description,
        bool $isDisplay,
        int $orderNo,
    ): ReleaseGroup {
        $errors = new FieldErrors();
        $releaseGroupIdVo = $errors->collect('releaseGroupId', static fn (): ReleaseGroupId => new ReleaseGroupId($releaseGroupId));
        $titleVo = $errors->collect('title', static fn (): ReleaseGroupTitle => new ReleaseGroupTitle($title));
        $typeVo = $errors->collect('typeValue', fn (): ReleaseGroupType => $this->toReleaseGroupType($typeValue));
        $descriptionVo = $errors->collect('description', static fn (): Description => new Description($description));
        $orderNoVo = $errors->collect('orderNo', static fn (): OrderNo => new OrderNo($orderNo));
        $errors->throwIfFailed();

        assert(! is_null($releaseGroupIdVo) && ! is_null($titleVo) && ! is_null($typeVo) && ! is_null($descriptionVo) && ! is_null($orderNoVo));

        return new ReleaseGroup(
            $releaseGroupIdVo,
            $titleVo,
            $typeVo,
            $descriptionVo,
            $isDisplay,
            $orderNoVo,
        );
    }

    /**
     * @throws InvalidDomainException
     */
    private function toReleaseGroupType(int $typeValue): ReleaseGroupType
    {
        return ReleaseGroupType::tryFrom($typeValue) ?? throw new InvalidDomainException("不正なリリースグループ種別です: {$typeValue}");
    }
}
