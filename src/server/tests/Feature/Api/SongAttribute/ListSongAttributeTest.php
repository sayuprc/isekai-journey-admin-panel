<?php

declare(strict_types=1);

namespace Tests\Feature\Api\SongAttribute;

use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\SongAttribute;
use Song\Route\SongAttributeRouteMap;
use Tests\Feature\Api\WithAuth;
use Tests\Support\DatabaseTestCase;

class ListSongAttributeTest extends DatabaseTestCase
{
    use WithAuth;

    #[Test]
    public function showList(): void
    {
        $this->withAuth()
            ->get(route(SongAttributeRouteMap::List))
            ->assertStatus(200)
            ->assertExactJson([
                'attributes' => collect(SongAttribute::cases())
                    ->map(fn (SongAttribute $attribute): array => [
                        'name' => $attribute->getName(),
                        'value' => $attribute->value,
                    ])
                    ->all(),
            ]);
    }
}
