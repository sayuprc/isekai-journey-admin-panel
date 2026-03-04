<?php

declare(strict_types=1);

namespace Tests\Feature\Api\SongAttribute;

use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\SongAttribute;
use SongAttribute\Route\SongAttributeRouteMap;
use Tests\Feature\Api\WithAuth;
use Tests\TestCase;

class ListSongAttributeTest extends TestCase
{
    use WithAuth;

    #[Test]
    public function showList(): void
    {
        $this->withAuth()
            ->get(route(SongAttributeRouteMap::List))
            ->assertStatus(200)
            ->assertExactJson([
                'songAttributes' => collect(SongAttribute::cases())
                    ->map(fn (SongAttribute $songAttribute): array => [
                        'name' => $songAttribute->getName(),
                        'value' => $songAttribute->value,
                    ])
                    ->all(),
            ]);
    }
}
