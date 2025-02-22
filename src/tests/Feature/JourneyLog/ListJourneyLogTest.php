<?php

declare(strict_types=1);

namespace Tests\Feature\JourneyLog;

use App\Models\User;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use JourneyLog\Domain\Entities\FromOn;
use JourneyLog\Domain\Entities\JourneyLog;
use JourneyLog\Domain\Entities\JourneyLogId;
use JourneyLog\Domain\Entities\JourneyLogLink;
use JourneyLog\Domain\Entities\JourneyLogLinkId;
use JourneyLog\Domain\Entities\JourneyLogLinkName;
use JourneyLog\Domain\Entities\JourneyLogLinkTypeId;
use JourneyLog\Domain\Entities\OrderNo;
use JourneyLog\Domain\Entities\Period;
use JourneyLog\Domain\Entities\Story;
use JourneyLog\Domain\Entities\ToOn;
use JourneyLog\Domain\Entities\Url;
use JourneyLog\Domain\Repositories\JourneyLogRepositoryInterface;
use Mockery;
use Mockery\LegacyMockInterface;
use PHPUnit\Framework\Attributes\Test;
use Shared\Route\RouteMap;
use Tests\TestCase;

class ListJourneyLogTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    private JourneyLogRepositoryInterface&LegacyMockInterface $journeyLogRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'user_id' => Str::uuid()->toString(),
        ]);

        $this->journeyLogRepository = Mockery::mock(JourneyLogRepositoryInterface::class);
    }

    #[Test]
    public function notLoggedIn(): void
    {
        $this->get(route(RouteMap::LIST_JOURNEY_LOGS))
            ->assertStatus(302)
            ->assertRedirect(route(RouteMap::SHOW_LOGIN_FORM));
    }

    #[Test]
    public function showList(): void
    {
        $uuid = $this->generateUuid();

        $this->journeyLogRepository->shouldReceive('listJourneyLogs')
            ->andReturn([
                new JourneyLog(
                    new JourneyLogId($uuid),
                    new Story('軌跡 A'),
                    new Period(new FromOn(new DateTimeImmutable()), new ToOn(new DateTimeImmutable())),
                    new OrderNo(0),
                    [],
                ),
                new JourneyLog(
                    new JourneyLogId($uuid),
                    new Story('軌跡 B'),
                    new Period(new FromOn(new DateTimeImmutable()), new ToOn(new DateTimeImmutable())),
                    new OrderNo(0),
                    [
                        new JourneyLogLink(
                            new JourneyLogLinkId($uuid),
                            new JourneyLogLinkName('管理画面'),
                            new Url('https://local.admin.journey.isekaijoucho.fan'),
                            new OrderNo(0),
                            new JourneyLogLinkTypeId($uuid),
                        ),
                    ],
                ),
            ])
            ->once();

        $this->app->bind(
            JourneyLogRepositoryInterface::class,
            fn (): JourneyLogRepositoryInterface => $this->journeyLogRepository,
        );

        $response = $this->actingAs($this->user)
            ->get(route(RouteMap::LIST_JOURNEY_LOGS))
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
        $this->journeyLogRepository->shouldReceive('listJourneyLogs')
            ->andReturn([])
            ->once();

        $this->app->bind(
            JourneyLogRepositoryInterface::class,
            fn (): JourneyLogRepositoryInterface => $this->journeyLogRepository,
        );

        $response = $this->actingAs($this->user)
            ->get(route(RouteMap::LIST_JOURNEY_LOGS))
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
