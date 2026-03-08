<?php

declare(strict_types=1);

namespace Creator\Application\UseCase\List;

enum Sort: string
{
    case name = 'name';

    case orderNo = 'orderNo';
}
