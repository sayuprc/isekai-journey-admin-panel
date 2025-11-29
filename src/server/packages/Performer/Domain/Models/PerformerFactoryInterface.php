<?php

declare(strict_types=1);

namespace Performer\Domain\Models;

interface PerformerFactoryInterface
{
    /**
     * @param positive-int $orderNo
     */
    public function create(string $performerName, int $orderNo): Performer;
}
