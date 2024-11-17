<?php

declare(strict_types=1);

namespace Tests\Feature\JourneyLog;

use App\Features\JourneyLog\Domain\Entities\JourneyLog;
use App\Features\JourneyLog\Domain\Repositories\JourneyLogRepositoryInterface;
use App\Features\JourneyLogLinkType\Domain\Repositories\JourneyLogLinkTypeRepositoryInterface;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Mockery;
use Mockery\LegacyMockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateJourneyLogTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    private JourneyLogRepositoryInterface&LegacyMockInterface $journeyLogRepository;

    private JourneyLogLinkTypeRepositoryInterface&LegacyMockInterface $journeyLogLinkTypeRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'user_id' => Str::uuid()->toString(),
        ]);

        $this->journeyLogRepository = Mockery::mock(JourneyLogRepositoryInterface::class);
        $this->journeyLogLinkTypeRepository = Mockery::mock(JourneyLogLinkTypeRepositoryInterface::class);
    }

    #[Test]
    public function notLoggedIn(): void
    {
        $this->get(route('journey-logs.create.index'))
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    #[Test]
    public function showCreateForm(): void
    {
        $this->journeyLogLinkTypeRepository->shouldReceive('listJourneyLogLinkTypes')
            ->andReturn([])
            ->once();

        $this->app->bind(
            JourneyLogLinkTypeRepositoryInterface::class,
            fn (): JourneyLogLinkTypeRepositoryInterface => $this->journeyLogLinkTypeRepository,
        );

        $response = $this->actingAs($this->user)
            ->get(route('journey-logs.create.index'))
            ->assertStatus(200);

        $data = $response->getOriginalContent()->getData();

        $this->assertCount(0, $data['journeyLogLinkTypes']);
    }

    #[Test]
    public function canCreate(): void
    {
        $this->journeyLogRepository->shouldReceive('createJourneyLog')
            ->with(Mockery::on(function (JourneyLog $arg): bool {
                return $arg->journeyLogId->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'
                    && $arg->story->value === '軌跡'
                    && $arg->period->fromOn->format('Y-m-d') === '2019-12-09'
                    && $arg->period->toOn->format('Y-m-d') === '2019-12-09'
                    && $arg->orderNo->value === 1
                    && count($arg->journeyLogLinks) === 1
                    && $arg->journeyLogLinks[0]->journeyLogLinkId->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'
                    && $arg->journeyLogLinks[0]->journeyLogLinkName->value === '管理画面'
                    && $arg->journeyLogLinks[0]->url->value === 'https://local.admin.journey.isekaijoucho.fan'
                    && $arg->journeyLogLinks[0]->orderNo->value === 1
                    && $arg->journeyLogLinks[0]->journeyLogLinkTypeId->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
            }))
            ->once();

        $this->app->bind(
            JourneyLogRepositoryInterface::class,
            fn (): JourneyLogRepositoryInterface => $this->journeyLogRepository,
        );

        $this->actingAs($this->user)
            ->post(route('journey-logs.create.handle'), [
                'story' => '軌跡',
                'from_on' => '2019-12-09',
                'to_on' => '2019-12-09',
                'order_no' => '1',
                'journey_log_links' => [
                    [
                        'journey_log_link_name' => '管理画面',
                        'url' => 'https://local.admin.journey.isekaijoucho.fan',
                        'order_no' => '1',
                        'journey_log_link_type_id' => 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA',
                    ],
                ],
            ])
            ->assertStatus(302)
            ->assertLocation(route('journey-logs.index'))
            ->assertSessionHas('message', '登録完了しました');
    }

    #[Test]
    public function emptyParameters(): void
    {
        $this->actingAs($this->user)
            ->post(route('journey-logs.create.handle'), [
                'story' => '',
                'from_on' => '',
                'to_on' => '',
                'order_no' => '',
                'journey_log_links' => [
                    [
                        'journey_log_link_name' => '',
                        'url' => '',
                        'order_no' => '',
                        'journey_log_link_type_id' => '',
                    ],
                ],
            ])
            ->assertStatus(302)
            ->assertSessionHasErrors([
                'story',
                'from_on',
                'to_on',
                'order_no',
                'journey_log_links.0.journey_log_link_name',
                'journey_log_links.0.url',
                'journey_log_links.0.order_no',
                'journey_log_links.0.journey_log_link_type_id',
            ]);
    }

    #[Test]
    public function invalidFormatDate(): void
    {
        $this->actingAs($this->user)
            ->post(route('journey-logs.create.handle'), [
                'story' => '軌跡',
                'from_on' => '2019/12/09',
                'to_on' => '2019/12/09',
                'order_no' => '1',
            ])
            ->assertStatus(302)
            ->assertSessionHasErrors([
                'from_on',
                'to_on',
            ]);
    }

    #[Test]
    public function inversionDate(): void
    {
        $this->actingAs($this->user)
            ->post(route('journey-logs.create.handle'), [
                'story' => '軌跡',
                'from_on' => '2019/12/09',
                'to_on' => '2019/12/08',
                'order_no' => '1',
            ])
            ->assertStatus(302)
            ->assertSessionHasErrors([
                'from_on',
                'to_on',
            ]);
    }
}
