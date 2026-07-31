<?php

declare(strict_types=1);

namespace Song\Domain\Services;

use Song\Domain\Models\Tag\SongTag;
use Song\Domain\Models\Tag\SongTagId;
use Song\Domain\Models\Tag\SongTagName;
use Song\Domain\Models\Tag\SongTagRepositoryInterface;
use Support\Contracts\Uuid\UuidGeneratorInterface;
use Support\Domain\Exceptions\BusinessRuleViolationException;
use Support\Domain\Exceptions\DomainValidationException;
use Support\Domain\Validation\FieldErrors;
use Support\Domain\ValueObjects\OrderNo;

class SongTagIntegrityService
{
    public function __construct(
        private readonly UuidGeneratorInterface $generator,
        private readonly SongTagRepositoryInterface $repository,
    ) {
    }

    /**
     * @throws DomainValidationException
     * @throws BusinessRuleViolationException
     */
    public function prepareForCreate(string $name): SongTag
    {
        $tag = $this->build(
            $this->generator->generate(),
            $name,
            // 更新時に同じ値になることを防ぐために +10 で採番
            $this->repository->getMaxOrderNo() + 10,
        );

        if (! is_null($this->repository->findByName($tag->name))) {
            throw new BusinessRuleViolationException(sprintf('すでに使われている名前です "%s"', $name));
        }

        return $tag;
    }

    /**
     * @throws DomainValidationException
     * @throws BusinessRuleViolationException
     */
    public function prepareForUpdate(string $songTagId, string $name, int $orderNo): SongTag
    {
        $tag = $this->build($songTagId, $name, $orderNo);

        if (! is_null($found = $this->repository->findByName($tag->name)) && ! $found->equals($tag)) {
            throw new BusinessRuleViolationException(sprintf('すでに使われている名前です "%s"', $name));
        }

        return $tag;
    }

    private function build(string $songTagId, string $name, int $orderNo): SongTag
    {
        $errors = new FieldErrors();
        $songTagIdVo = $errors->collect('songTagId', static fn (): SongTagId => new SongTagId($songTagId));
        $nameVo = $errors->collect('name', static fn (): SongTagName => new SongTagName($name));
        $orderNoVo = $errors->collect('orderNo', static fn (): OrderNo => new OrderNo($orderNo));
        $errors->throwIfFailed();

        assert(! is_null($songTagIdVo) && ! is_null($nameVo) && ! is_null($orderNoVo));

        return new SongTag($songTagIdVo, $nameVo, $orderNoVo);
    }
}
