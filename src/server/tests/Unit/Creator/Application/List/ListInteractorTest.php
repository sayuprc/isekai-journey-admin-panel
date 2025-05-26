<?php

declare(strict_types=1);

namespace Tests\Unit\Creator\Application\List;

use Creator\Application\List\ListInteractor;
use Creator\Domain\Models\Creator;
use Creator\Domain\Models\CreatorId;
use Creator\Domain\Models\CreatorName;
use Creator\Domain\Repositories\CreatorRepositoryInterface;
use Creator\UseCases\List\ListOutputData;
use Creator\UseCases\List\ListUseCaseInterface;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ListInteractorTest extends TestCase
{
    private CreatorRepositoryInterface&MockInterface $repository;

    private ListInteractor $interactor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(CreatorRepositoryInterface::class);

        $this->interactor = new ListInteractor($this->repository);
    }

    #[Test]
    public function isImplementsSpecificInterface(): void
    {
        $this->assertInstanceOf(ListUseCaseInterface::class, $this->interactor);
    }

    #[Test]
    public function emptyCreators(): void
    {
        $this->repository->shouldReceive('all')
            ->andReturn([])
            ->once();

        $response = $this->interactor->handle();

        $this->assertInstanceOf(ListOutputData::class, $response);

        $this->assertCount(0, $response->creators);
    }

    #[Test]
    public function nonEmptyCreators(): void
    {
        $this->repository->shouldReceive('all')
            ->andReturn([
                new Creator(
                    new CreatorId('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
                    new CreatorName('クリエイターA')
                ),
                new Creator(
                    new CreatorId('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'),
                    new CreatorName('クリエイターB')
                ),
            ])
            ->once();

        $response = $this->interactor->handle();

        $this->assertInstanceOf(ListOutputData::class, $response);

        $this->assertCount(2, $response->creators);

        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $response->creators[0]->creatorId->value);
        $this->assertSame('クリエイターA', $response->creators[0]->creatorName->value);
        $this->assertSame('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', $response->creators[1]->creatorId->value);
        $this->assertSame('クリエイターB', $response->creators[1]->creatorName->value);
    }
}
