<?php

declare(strict_types=1);

namespace Tests\Feature\JourneyLogLinkType;

use App\Models\User;
use App\Shared\Route\RouteMap;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use JourneyLogLinkType\Domain\Entities\JourneyLogLinkType;
use JourneyLogLinkType\Domain\Entities\JourneyLogLinkTypeId;
use JourneyLogLinkType\Domain\Entities\JourneyLogLinkTypeName;
use JourneyLogLinkType\Domain\Entities\OrderNo;
use JourneyLogLinkType\Domain\Repositories\JourneyLogLinkTypeRepositoryInterface;
use Mockery;
use Mockery\LegacyMockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ListJourneyLogLinkTypeTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    private JourneyLogLinkTypeRepositoryInterface&LegacyMockInterface $journeyLogLinkTypeRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'user_id' => Str::uuid()->toString(),
        ]);

        $this->journeyLogLinkTypeRepository = Mockery::mock(JourneyLogLinkTypeRepositoryInterface::class);
    }

    #[Test]
    public function notLoggedIn(): void
    {
        $this->get(route(RouteMap::LIST_JOURNEY_LOG_LINK_TYPE))
            ->assertStatus(302)
            ->assertRedirect(route(RouteMap::SHOW_LOGIN_FORM));
    }

    #[Test]
    public function showList(): void
    {
        $uuid = $this->generateUuid();

        $this->journeyLogLinkTypeRepository->shouldReceive('listJourneyLogLinkTypes')
            ->andReturn([
                new JourneyLogLinkType(
                    new JourneyLogLinkTypeId($uuid),
                    new JourneyLogLinkTypeName('名前1'),
                    new OrderNo(1),
                ),
                new JourneyLogLinkType(
                    new JourneyLogLinkTypeId($uuid),
                    new JourneyLogLinkTypeName('名前2'),
                    new OrderNo(2),
                ),
            ])
            ->once();

        $this->app->bind(
            JourneyLogLinkTypeRepositoryInterface::class,
            fn (): JourneyLogLinkTypeRepositoryInterface => $this->journeyLogLinkTypeRepository,
        );

        $response = $this->actingAs($this->user)
            ->get(route(RouteMap::LIST_JOURNEY_LOG_LINK_TYPE))
            ->assertStatus(200)
            ->assertViewIs('journeyLogLinkTypes.list.index');

        $data = $response->getOriginalContent()->getData();

        $this->assertSame([
            '名前',
            '表示順',
            '',
        ], $data['heads']);

        $this->assertCount(2, $data['journeyLogLinkTypes']);
    }

    #[Test]
    public function showEmptyList(): void
    {
        $this->journeyLogLinkTypeRepository->shouldReceive('listJourneyLogLinkTypes')
            ->andReturn([])
            ->once();

        $this->app->bind(
            JourneyLogLinkTypeRepositoryInterface::class,
            fn (): JourneyLogLinkTypeRepositoryInterface => $this->journeyLogLinkTypeRepository,
        );

        $response = $this->actingAs($this->user)
            ->get(route(RouteMap::LIST_JOURNEY_LOG_LINK_TYPE))
            ->assertStatus(200)
            ->assertViewIs('journeyLogLinkTypes.list.index');

        $data = $response->getOriginalContent()->getData();

        $this->assertSame([
            '名前',
            '表示順',
            '',
        ], $data['heads']);

        $this->assertCount(0, $data['journeyLogLinkTypes']);
    }
}
