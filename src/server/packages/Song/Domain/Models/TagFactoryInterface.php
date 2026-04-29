<?php

declare(strict_types=1);

namespace Song\Domain\Models;

use Support\Domain\ValueObjects\OrderNo;

interface TagFactoryInterface
{
    public function create(TagId $tagId, TagName $name, OrderNo $orderNo): Tag;
}
