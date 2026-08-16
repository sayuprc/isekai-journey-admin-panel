<?php

declare(strict_types=1);

namespace Place\Infrastructures;

use Override;
use Place\Domain\Models\PlaceId;
use Place\Domain\Services\PlaceUsageCheckerInterface;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Infrastructures\Database\QueryFactory;

readonly class PlaceUsageChecker implements PlaceUsageCheckerInterface
{
    public function __construct(
        private QueryFactory $queryFactory,
        private UuidConverterInterface $converter,
    ) {
    }

    #[Override]
    public function isUsed(PlaceId $placeId): bool
    {
        $rows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->from('event_places')
                ->where('place_id', '=', $this->converter->toBin($placeId->value))
                ->limit(1),
        );

        return $rows !== [];
    }
}
