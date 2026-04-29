<?php

declare(strict_types=1);

namespace SongTag\Infrastructures;

use Song\Domain\Models\Tag;
use Song\Domain\Models\TagFactoryInterface;
use Song\Domain\Models\TagId;
use Song\Domain\Models\TagName;
use Support\Domain\ValueObjects\OrderNo;

class TagFactory implements TagFactoryInterface
{
    public function create(TagId $tagId, TagName $name, OrderNo $orderNo): Tag
    {
        return new Tag($tagId, $name, $orderNo);
    }
}
