<?php

declare(strict_types=1);

namespace Tests\Feature\Api\SongType;

use PHPUnit\Framework\Attributes\Test;
use SongType\Domain\Models\SongType;
use SongType\Route\SongTypeRouteMap;
use Tests\Feature\Api\WithAuth;
use Tests\TestCase;

class ListSongTypeTest extends TestCase
{
    use WithAuth;

    #[Test]
    public function showList(): void
    {
        $this->withAuth()
            ->get(route(SongTypeRouteMap::List))
            ->assertStatus(200)
            ->assertExactJson([
                'songTypes' => collect(SongType::cases())
                    ->map(fn (SongType $songType): array => [
                        'name' => $songType->getName(),
                        'value' => $songType->value,
                    ])
                    ->all(),
            ]);
    }
}
