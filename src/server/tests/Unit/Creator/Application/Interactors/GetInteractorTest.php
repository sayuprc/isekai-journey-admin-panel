<?php

declare(strict_types=1);

namespace Tests\Unit\Creator\Application\Interactors;

use Creator\Application\Interactors\GetInteractor;
use Creator\Application\UseCase\Get\GetInputData;
use Creator\Domain\Models\CreatorId;
use Creator\Domain\Models\CreatorRepositoryInterface;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class GetInteractorTest extends TestCase
{
    use EntityFactory;

    private CreatorRepositoryInterface&MockInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(CreatorRepositoryInterface::class);
    }

    #[Test]
    public function getCreator(): void
    {
        $this->repository->shouldReceive('find')
            ->withArgs(fn (CreatorId $arg): bool => $arg->value === 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB')
            ->andReturn($this->createCreator('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', 'クリエイター名'))
            ->once();

        $result = $this->getInstance()->handle(new GetInputData('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'));

        $this->assertTrue($result->isOk());

        $response = $result->unwrap();

        $this->assertSame('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', $response->creator->creatorId->value);
        $this->assertSame('クリエイター名', $response->creator->creatorName->value);
    }

    #[Test]
    public function failureGetCreator(): void
    {
        $this->repository->shouldReceive('find')
            ->withArgs(fn (CreatorId $arg): bool => $arg->value === 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB')
            ->andReturnNull()
            ->once();

        $result = $this->getInstance()->handle(new GetInputData('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'));

        $this->assertFalse($result->isOk());

        $this->assertSame('Creator not found: BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', $result->unwrapErr());
    }

    private function getInstance(): GetInteractor
    {
        return new GetInteractor($this->repository);
    }
}
