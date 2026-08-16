<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Place;

use PHPUnit\Framework\Attributes\Test;
use Place\Domain\Models\PlaceKind;
use Place\Route\PlaceRouteMap;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class SearchPlaceTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;
    use WithAuth;

    #[Test]
    public function searchAll(): void
    {
        $uuid = $this->generateUuid();

        $this->storePlaces($this->createPlace($uuid, 'テスト会場', PlaceKind::Physical));

        $this->withAuth()
            ->get(route(PlaceRouteMap::Search))
            ->assertStatus(200)
            ->assertExactJson([
                'places' => [
                    [
                        'placeId' => $uuid,
                        'name' => 'テスト会場',
                        'kind' => [
                            'name' => PlaceKind::Physical->getName(),
                            'value' => PlaceKind::Physical->value,
                        ],
                    ],
                ],
                'maxPage' => 1,
            ]);
    }

    #[Test]
    public function searchByName(): void
    {
        $uuid1 = $this->generateUuid();
        $uuid2 = $this->generateUuid();

        $this->storePlaces(
            $this->createPlace($uuid1, 'テスト会場1', PlaceKind::Physical),
            $this->createPlace($uuid2, 'テスト会場2', PlaceKind::Online),
        );

        $this->withAuth()
            ->get(route(PlaceRouteMap::Search, ['name' => 'テスト会場1']))
            ->assertStatus(200)
            ->assertExactJson([
                'places' => [
                    [
                        'placeId' => $uuid1,
                        'name' => 'テスト会場1',
                        'kind' => [
                            'name' => PlaceKind::Physical->getName(),
                            'value' => PlaceKind::Physical->value,
                        ],
                    ],
                ],
                'maxPage' => 1,
            ]);
    }

    #[Test]
    public function searchByKindValue(): void
    {
        $uuid1 = $this->generateUuid();
        $uuid2 = $this->generateUuid();

        $this->storePlaces(
            $this->createPlace($uuid1, '会場A', PlaceKind::Physical),
            $this->createPlace($uuid2, '配信先B', PlaceKind::Online),
        );

        $this->withAuth()
            ->get(route(PlaceRouteMap::Search, ['kindValue' => PlaceKind::Online->value]))
            ->assertStatus(200)
            ->assertExactJson([
                'places' => [
                    [
                        'placeId' => $uuid2,
                        'name' => '配信先B',
                        'kind' => [
                            'name' => PlaceKind::Online->getName(),
                            'value' => PlaceKind::Online->value,
                        ],
                    ],
                ],
                'maxPage' => 1,
            ]);
    }

    #[Test]
    public function searchSortByNameDesc(): void
    {
        $uuid1 = $this->generateUuid();
        $uuid2 = $this->generateUuid();

        $this->storePlaces(
            $this->createPlace($uuid1, 'あ', PlaceKind::Physical),
            $this->createPlace($uuid2, 'い', PlaceKind::Physical),
        );

        $this->withAuth()
            ->get(route(PlaceRouteMap::Search, ['sort' => 'name', 'order' => 'desc']))
            ->assertStatus(200)
            ->assertExactJson([
                'places' => [
                    [
                        'placeId' => $uuid2,
                        'name' => 'い',
                        'kind' => [
                            'name' => PlaceKind::Physical->getName(),
                            'value' => PlaceKind::Physical->value,
                        ],
                    ],
                    [
                        'placeId' => $uuid1,
                        'name' => 'あ',
                        'kind' => [
                            'name' => PlaceKind::Physical->getName(),
                            'value' => PlaceKind::Physical->value,
                        ],
                    ],
                ],
                'maxPage' => 1,
            ]);
    }
}
