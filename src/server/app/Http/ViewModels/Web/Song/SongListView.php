<?php

declare(strict_types=1);

namespace App\Http\ViewModels\Web\Song;

class SongListView
{
    public function __construct(
        public readonly string $songId,
        public readonly string $title,
        public readonly string $description,
        public readonly int $orderNo,
    ) {
    }
}
