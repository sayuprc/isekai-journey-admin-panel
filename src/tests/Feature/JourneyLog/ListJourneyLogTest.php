<?php

declare(strict_types=1);

namespace Tests\Feature\JourneyLog;

use App\Features\JourneyLog\Domain\Entities\JourneyLog;
use App\Features\JourneyLog\Domain\Entities\JourneyLogId;
use App\Features\JourneyLog\Domain\Entities\JourneyLogLink;
use App\Features\JourneyLog\Domain\Entities\JourneyLogLinkId;
use App\Features\JourneyLog\Domain\Entities\JourneyLogLinkName;
use App\Features\JourneyLog\Domain\Entities\OrderNo;
use App\Features\JourneyLog\Domain\Entities\Period;
use App\Features\JourneyLog\Domain\Entities\Story;
use App\Features\JourneyLog\Domain\Entities\Url;
use App\Features\JourneyLog\Domain\Repositories\JourneyLogRepositoryInterface;
use App\Features\JourneyLogLinkType\Domain\Entities\JourneyLogLinkTypeId;
use App\Models\User;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ListJourneyLogTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'user_id' => Str::uuid()->toString(),
        ]);
    }

    #[Test]
    public function notLoggedIn(): void
    {
        $this->get(route('journey-logs.index'))
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    #[Test]
    public function showList(): void
    {
        $this->app->bind(
            JourneyLogRepositoryInterface::class,
            fn (): JourneyLogRepositoryInterface => $this->getJourneyLogRepository(listJourneyLogs: fn (): array => [
                new JourneyLog(
                    new JourneyLogId('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
                    new Story('軌跡 A'),
                    new Period(new DateTimeImmutable(), new DateTimeImmutable()),
                    new OrderNo(0),
                    [],
                ),
                new JourneyLog(
                    new JourneyLogId('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAB'),
                    new Story('軌跡 B'),
                    new Period(new DateTimeImmutable(), new DateTimeImmutable()),
                    new OrderNo(0),
                    [
                        new JourneyLogLink(
                            new JourneyLogLinkId('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAC'),
                            new JourneyLogLinkName('管理画面'),
                            new Url('https://local.admin.journey.isekaijoucho.fan'),
                            new OrderNo(0),
                            new JourneyLogLinkTypeId('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAD')
                        ),
                    ],
                ),
            ])
        );

        $response = $this->actingAs($this->user)
            ->get(route('journey-logs.index'))
            ->assertStatus(200)
            ->assertViewIs('journeyLogs.list.index');

        $data = $response->getOriginalContent()->getData();

        $this->assertSame([
            '内容',
            '期間',
            '表示順',
            '',
        ], $data['heads']);

        $this->assertCount(2, $data['journeyLogs']);
    }

    #[Test]
    public function showEmptyList(): void
    {
        $this->app->bind(
            JourneyLogRepositoryInterface::class,
            fn (): JourneyLogRepositoryInterface => $this->getJourneyLogRepository(listJourneyLogs: fn (): array => [])
        );

        $response = $this->actingAs($this->user)
            ->get(route('journey-logs.index'))
            ->assertStatus(200)
            ->assertViewIs('journeyLogs.list.index');

        $data = $response->getOriginalContent()->getData();

        $this->assertSame([
            '内容',
            '期間',
            '表示順',
            '',
        ], $data['heads']);

        $this->assertCount(0, $data['journeyLogs']);
    }
}
