<?php

declare(strict_types=1);

namespace Tests\Unit\Creator\Application\Interactors;

use Creator\Application\Interactors\ListInteractor;
use Creator\Domain\Models\CreatorRepositoryInterface;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class ListInteractorTest extends TestCase
{
    use EntityFactory;

    private CreatorRepositoryInterface&MockInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(CreatorRepositoryInterface::class);
    }

    #[Test]
    public function emptyCreators(): void
    {
        $this->repository->shouldReceive('all')
            ->andReturn([])
            ->once();

        $response = $this->getInstance()->handle();

        $this->assertCount(0, $response->creators);
    }

    #[Test]
    public function nonEmptyCreators(): void
    {
        $this->repository->shouldReceive('all')
            ->andReturn([
                $this->createCreator('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', 'クリエイターA'),
                $this->createCreator('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', 'クリエイターB'),
            ])
            ->once();

        $response = $this->getInstance()->handle();

        $this->assertCount(2, $response->creators);

        $this->assertSame('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $response->creators[0]->creatorId->value);
        $this->assertSame('クリエイターA', $response->creators[0]->creatorName->value);
        $this->assertSame('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', $response->creators[1]->creatorId->value);
        $this->assertSame('クリエイターB', $response->creators[1]->creatorName->value);
    }

    private function getInstance(): ListInteractor
    {
        return new ListInteractor($this->repository);
    }
}
