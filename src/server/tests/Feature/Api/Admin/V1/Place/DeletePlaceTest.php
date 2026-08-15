<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Place;

use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Place\Domain\Models\PlaceKind;
use Place\Route\PlaceRouteMap;
use Support\Contracts\Uuid\UuidConverterInterface;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class DeletePlaceTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;
    use WithAuth;

    #[Test]
    public function canDelete(): void
    {
        $uuid = $this->generateUuid();

        $this->storePlaces($this->createPlace($uuid, '会場', PlaceKind::Physical));

        $this->withAuth()
            ->delete(route(PlaceRouteMap::Delete, $uuid))
            ->assertStatus(204);
    }

    #[Test]
    public function invalidPlaceId(): void
    {
        $this->withAuth()
            ->delete(route(PlaceRouteMap::Delete, 'invalid-id'))
            ->assertStatus(422)
            ->assertExactJson([
                'code' => 'validation_failed',
                'message' => '入力内容に誤りがあります',
                'details' => [
                    [
                        'field' => '',
                        'message' => '予期せぬエラー',
                    ],
                ],
            ]);
    }

    #[Test]
    public function cannotDeleteWhenUsedInEvent(): void
    {
        $placeId = $this->generateUuid();
        $eventId = $this->generateUuid();

        $this->storePlaces($this->createPlace($placeId, '会場', PlaceKind::Physical));
        $this->linkPlaceToEvent($placeId, $eventId);

        $this->withAuth()
            ->delete(route(PlaceRouteMap::Delete, $placeId))
            ->assertStatus(400)
            ->assertExactJson([
                'code' => 'business_rule_violation',
                'message' => 'この場所は出来事に使用されているため削除できません',
            ]);
    }

    private function linkPlaceToEvent(string $placeId, string $eventId): void
    {
        $converter = $this->app->make(UuidConverterInterface::class);
        $now = now()->toDateTimeString();

        DB::table('events')->insert([
            'event_id' => $converter->toBin($eventId),
            'title' => 'テスト出来事',
            'type' => 1,
            'started_at' => $now,
            'ended_at' => $now,
            'description' => '',
            'is_display' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('event_places')->insert([
            'event_id' => $converter->toBin($eventId),
            'place_id' => $converter->toBin($placeId),
        ]);
    }
}
