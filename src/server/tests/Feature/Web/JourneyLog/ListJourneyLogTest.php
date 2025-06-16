<?php

declare(strict_types=1);

namespace Tests\Feature\Web\JourneyLog;

use App\Models\User;
use Auth\Route\AuthRouteMap;
use DateType\ImmutableDate;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use JourneyLog\Domain\Models\FromOn;
use JourneyLog\Domain\Models\JourneyLog;
use JourneyLog\Domain\Models\JourneyLogId;
use JourneyLog\Domain\Models\JourneyLogLink\JourneyLogLink;
use JourneyLog\Domain\Models\JourneyLogLink\JourneyLogLinkId;
use JourneyLog\Domain\Models\JourneyLogLink\JourneyLogLinkName;
use JourneyLog\Domain\Models\Period;
use JourneyLog\Domain\Models\Story;
use JourneyLog\Domain\Models\ToOn;
use JourneyLog\Domain\Models\Url;
use JourneyLog\Domain\Repositories\JourneyLogRepositoryInterface;
use JourneyLog\Route\JourneyLogRouteMap;
use JourneyLogLinkType\Domain\Models\JourneyLogLinkTypeId;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Support\Domain\ValueObjects\OrderNo;
use Tests\TestCase;

class ListJourneyLogTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    private JourneyLogRepositoryInterface&MockInterface $repository;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'user_id' => Str::uuid()->toString(),
        ]);

        $this->repository = Mockery::mock(JourneyLogRepositoryInterface::class);

        $this->app->bind(JourneyLogRepositoryInterface::class, fn (): JourneyLogRepositoryInterface => $this->repository);
    }

    #[Test]
    public function notLoggedIn(): void
    {
        $this->get(route(JourneyLogRouteMap::List))
            ->assertStatus(302)
            ->assertRedirect(route(AuthRouteMap::ShowLoginForm));
    }

    #[Test]
    public function showList(): void
    {
        $uuid = $this->generateUuid();

        $this->repository->shouldReceive('all')
            ->andReturn([
                new JourneyLog(
                    new JourneyLogId($uuid),
                    new Story('軌跡 A'),
                    new Period(new FromOn(new ImmutableDate()), new ToOn(new ImmutableDate())),
                    new OrderNo(1),
                    [],
                ),
                new JourneyLog(
                    new JourneyLogId($uuid),
                    new Story('軌跡 B'),
                    new Period(new FromOn(new ImmutableDate()), new ToOn(new ImmutableDate())),
                    new OrderNo(2),
                    [
                        new JourneyLogLink(
                            new JourneyLogLinkId($uuid),
                            new JourneyLogLinkName('管理画面'),
                            new Url('https://local.admin.journey.isekaijoucho.fan'),
                            new OrderNo(1),
                            new JourneyLogLinkTypeId($uuid),
                        ),
                    ],
                ),
            ])
            ->once();

        $response = $this->actingAs($this->user)
            ->get(route(JourneyLogRouteMap::List))
            ->assertStatus(200)
            ->assertViewIs('journeyLogs.list.index');

        $data = $response->getOriginalContent()->getData();

        $this->assertSame([
            '期間',
            '内容',
            '表示順',
            '',
        ], $data['heads']);

        $this->assertSame([
            'order' => [[0, 'asc']],
        ], $data['config']);

        $this->assertCount(2, $data['journeyLogs']);
    }

    #[Test]
    public function showEmptyList(): void
    {
        $this->repository->shouldReceive('all')
            ->andReturn([])
            ->once();

        $response = $this->actingAs($this->user)
            ->get(route(JourneyLogRouteMap::List))
            ->assertStatus(200)
            ->assertViewIs('journeyLogs.list.index');

        $data = $response->getOriginalContent()->getData();

        $this->assertSame([
            '期間',
            '内容',
            '表示順',
            '',
        ], $data['heads']);

        $this->assertSame([
            'order' => [[0, 'asc']],
        ], $data['config']);

        $this->assertCount(0, $data['journeyLogs']);
    }
}
