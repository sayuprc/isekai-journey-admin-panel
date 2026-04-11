<?php

declare(strict_types=1);

namespace Song\Domain\Criteria;

enum SongTagSort: string
{
    case Name = 'name';

    case OrderNo = 'order_no';
}
