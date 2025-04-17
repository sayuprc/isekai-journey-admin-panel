<?php

declare(strict_types=1);

namespace App\Http\ViewModels\Web\Creator;

class CreatorView
{
    public function __construct(
        public readonly string $creatorId,
        public readonly string $creatorName,
    ) {
    }
}
