<?php

declare(strict_types=1);

namespace Tests\Feature\Web\JourneyLogLinkType;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use JourneyLogLinkType\Domain\Models\JourneyLogLinkType;
use JourneyLogLinkType\Domain\Models\JourneyLogLinkTypeId;
use JourneyLogLinkType\Domain\Models\JourneyLogLinkTypeName;
use JourneyLogLinkType\Domain\Repositories\JourneyLogLinkTypeRepositoryInterface;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Support\Domain\ValueObjects\OrderNo;
use Support\Route\RouteMap;
use Tests\TestCase;

class ListJourneyLogLinkTypeTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    private JourneyLogLinkTypeRepositoryInterface&MockInterface $repository;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'user_id' => Str::uuid()->toString(),
        ]);

        $this->repository = Mockery::mock(JourneyLogLinkTypeRepositoryInterface::class);

        $this->app->bind(
            JourneyLogLinkTypeRepositoryInterface::class,
            fn (): JourneyLogLinkTypeRepositoryInterface => $this->repository
        );
    }

    #[Test]
    public function notLoggedIn(): void
    {
        $this->get(route(RouteMap::ListJourneyLogLinkType))
            ->assertStatus(302)
            ->assertRedirect(route(RouteMap::ShowLoginForm));
    }

    #[Test]
    public function showList(): void
    {
        $uuid = $this->generateUuid();

        $this->repository->shouldReceive('all')
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

        $response = $this->actingAs($this->user)
            ->get(route(RouteMap::ListJourneyLogLinkType))
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
        $this->repository->shouldReceive('all')
            ->andReturn([])
            ->once();

        $response = $this->actingAs($this->user)
            ->get(route(RouteMap::ListJourneyLogLinkType))
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
