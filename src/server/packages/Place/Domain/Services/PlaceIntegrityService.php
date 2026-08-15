<?php

declare(strict_types=1);

namespace Place\Domain\Services;

use Place\Domain\Models\Place;
use Place\Domain\Models\PlaceId;
use Place\Domain\Models\PlaceKind;
use Place\Domain\Models\PlaceName;
use Place\Domain\Models\PlaceRepositoryInterface;
use Support\Contracts\Uuid\UuidGeneratorInterface;
use Support\Domain\Exceptions\BusinessRuleViolationException;

class PlaceIntegrityService
{
    public function __construct(
        private readonly UuidGeneratorInterface $generator,
        private readonly PlaceRepositoryInterface $repository,
    ) {
    }

    /**
     * @throws BusinessRuleViolationException
     */
    public function prepareForCreate(string $name, int $kind): Place
    {
        $place = $this->build($this->generator->generate(), $name, $kind);

        if (! is_null($this->repository->findByName($place->name))) {
            throw new BusinessRuleViolationException(sprintf('すでに使われている名前です "%s"', $name));
        }

        return $place;
    }

    /**
     * @throws BusinessRuleViolationException
     */
    public function prepareForUpdate(string $placeId, string $name, int $kind): Place
    {
        $place = $this->build($placeId, $name, $kind);

        if (! is_null($found = $this->repository->findByName($place->name)) && ! $found->equals($place)) {
            throw new BusinessRuleViolationException(sprintf('すでに使われている名前です "%s"', $name));
        }

        return $place;
    }

    private function build(string $placeId, string $name, int $kind): Place
    {
        return new Place(new PlaceId($placeId), new PlaceName($name), PlaceKind::from($kind));
    }
}
