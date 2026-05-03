<?php

declare(strict_types=1);

namespace Tests\Unit\Performer\Application\UseCase\List;

use Mockery;
use Mockery\MockInterface;
use Override;
use Performer\Application\UseCase\List\ListUseCase;
use Performer\Domain\Models\PerformerRepositoryInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class ListUseCaseTest extends TestCase
{
    use EntityFactory;

    private MockInterface&PerformerRepositoryInterface $repository;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(PerformerRepositoryInterface::class);
    }

    #[Test]
    public function emptyPerformers(): void
    {
        $this->repository->shouldReceive('all')
            ->andReturn([])
            ->once();

        $result = $this->getInstance()->handle();
        $this->assertTrue($result->isOk());

        $response = $result->unwrap();

        $this->assertCount(0, $response->performers);
    }

    #[Test]
    public function nonEmptyPerformers(): void
    {
        $this->repository->shouldReceive('all')
            ->andReturn([
                $this->createPerformer('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', '共演者A', 1),
                $this->createPerformer('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', '共演者B', 2),
            ])
            ->once();

        $result = $this->getInstance()->handle();
        $this->assertTrue($result->isOk());

        $response = $result->unwrap();

        $this->assertCount(2, $response->performers);

        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $response->performers[0]->performerId->value);
        $this->assertSame('共演者A', $response->performers[0]->name->value);
        $this->assertSame(1, $response->performers[0]->orderNo->value);
        $this->assertSame('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', $response->performers[1]->performerId->value);
        $this->assertSame('共演者B', $response->performers[1]->name->value);
        $this->assertSame(2, $response->performers[1]->orderNo->value);
    }

    private function getInstance(): ListUseCase
    {
        return new ListUseCase(
            $this->authorizer(),
            $this->repository,
        );
    }
}
