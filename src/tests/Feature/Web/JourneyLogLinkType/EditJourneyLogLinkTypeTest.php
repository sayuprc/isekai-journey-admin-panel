<?php

declare(strict_types=1);

namespace Tests\Feature\Web\JourneyLogLinkType;

use App\Http\ViewModels\Web\JourneyLogLink\JourneyLogLinkTypeView;
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

class EditJourneyLogLinkTypeTest extends TestCase
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
        $uuid = $this->generateUuid();

        $this->get(route(RouteMap::ShowEditJourneyLogLinkTypeForm, ['journeyLogLinkTypeId' => $uuid]))
            ->assertStatus(302)
            ->assertRedirect(route(RouteMap::ShowLoginForm));
    }

    #[Test]
    public function withNotUuidStyleId(): void
    {
        $this->get(route(
            RouteMap::ShowEditJourneyLogLinkTypeForm,
            ['journeyLogLinkTypeId' => 'not-uuid-style-id']
        ))->assertStatus(404);
    }

    #[Test]
    public function showEditForm(): void
    {
        $uuid = $this->generateUuid();

        $this->repository->shouldReceive('find')
            ->with(Mockery::on(fn (JourneyLogLinkTypeId $arg): bool => $arg->value === $uuid))
            ->andReturn(new JourneyLogLinkType(
                new JourneyLogLinkTypeId($uuid),
                new JourneyLogLinkTypeName('名前'),
                new OrderNo(1),
            ))
            ->once();

        $response = $this->actingAs($this->user)
            ->get(route(RouteMap::ShowEditJourneyLogLinkTypeForm, ['journeyLogLinkTypeId' => $uuid]))
            ->assertStatus(200);

        $data = $response->getOriginalContent()->getData();

        $this->assertInstanceOf(JourneyLogLinkTypeView::class, $data['journeyLogLinkType']);
    }

    #[Test]
    public function failureShowEditForm(): void
    {
        $uuid = $this->generateUuid();

        $this->repository->shouldReceive('find')
            ->with(Mockery::on(fn (JourneyLogLinkTypeId $arg): bool => $arg->value === $uuid))
            ->andReturnNull()
            ->once();

        $this->actingAs($this->user)
            ->get(route(RouteMap::ShowEditJourneyLogLinkTypeForm, ['journeyLogLinkTypeId' => $uuid]))
            ->assertStatus(302)
            ->assertLocation(route(RouteMap::ListJourneyLogLinkType))
            ->assertInvalid(['message' => "JourneyLogLinkType not found: {$uuid}"]);
    }

    #[Test]
    public function canEdit(): void
    {
        $uuid = $this->generateUuid();

        $this->repository->shouldReceive('update')
            ->with(Mockery::on(
                fn (JourneyLogLinkType $arg): bool => $arg->journeyLogLinkTypeId->value === $uuid
                    && $arg->journeyLogLinkTypeName->value === '動画'
                    && $arg->orderNo->value === 1
            ))
            ->once();

        $this->actingAs($this->user)
            ->post(route(RouteMap::EditJourneyLogLinkType), [
                'journey_log_link_type_id' => $uuid,
                'journey_log_link_type_name' => '動画',
                'order_no' => '1',
            ])
            ->assertStatus(302)
            ->assertLocation(route(RouteMap::ListJourneyLogLinkType))
            ->assertSessionHas('message', '更新しました');
    }

    #[Test]
    public function emptyParameters(): void
    {
        $this->actingAs($this->user)
            ->post(route(RouteMap::EditJourneyLogLinkType), [
                'journey_log_link_type_id' => '',
                'journey_log_link_type_name' => '',
                'order_no' => '',
            ])
            ->assertStatus(302)
            ->assertSessionHasErrors([
                'journey_log_link_type_id',
                'journey_log_link_type_name',
                'order_no',
            ]);
    }
}
