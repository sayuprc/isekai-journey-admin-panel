<?php

declare(strict_types=1);

namespace Tests\Unit\Song\Application\UseCase;

use Closure;
use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\Test;
use Song\Application\Assemble\AssembledSong;
use Song\Application\Assemble\SongAssembler;
use Song\Application\UseCase\Delete\DeleteInputData;
use Song\Application\UseCase\Delete\DeleteUseCase;
use Song\Domain\Models\SongId;
use Song\Domain\Models\SongRepositoryInterface;
use Song\Domain\Models\SongType;
use Support\Contracts\AuditLog\AuditLogRecorderInterface;
use Support\Contracts\TransactionInterface;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class DeleteUseCaseTest extends TestCase
{
    use EntityFactory;

    private MockInterface&TransactionInterface $transaction;

    private MockInterface&SongRepositoryInterface $repository;

    private MockInterface&SongAssembler $assembler;

    private AuditLogRecorderInterface&MockInterface $recorder;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->transaction = Mockery::mock(TransactionInterface::class);
        $this->transaction->shouldReceive('scope')
            ->withArgs(fn (Closure $_) => true)
            ->andReturnUsing(fn (Closure $arg) => $arg())
            ->byDefault();
        $this->repository = Mockery::mock(SongRepositoryInterface::class);
        $this->assembler = Mockery::mock(SongAssembler::class);
        $this->recorder = Mockery::mock(AuditLogRecorderInterface::class);
        $this->recorder->shouldReceive('record')->byDefault();
    }

    #[Test]
    public function deleteSong(): void
    {
        $songId = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $song = $this->createSong($songId, '曲', '説明', null, SongType::Original, true, 1, [], []);

        $this->repository->shouldReceive('find')
            ->withArgs(fn (SongId $arg): bool => $arg->value === $songId)
            ->andReturn($song)
            ->once();

        $this->assembler->shouldReceive('assemble')
            ->with($song)
            ->andReturn(new AssembledSong($songId, '曲', '説明', null, 'Original', 1, true, 1, [], []))
            ->once();

        $this->repository->shouldReceive('delete')
            ->withArgs(fn (SongId $arg): bool => $arg->value === $songId)
            ->once();

        $result = $this->getInstance()->handle(new DeleteInputData($songId));

        $this->assertTrue($result->isOk());
    }

    private function getInstance(): DeleteUseCase
    {
        $context = $this->privilegedContext();

        return new DeleteUseCase(
            $this->authorizer($context),
            $this->transaction,
            $this->repository,
            $this->assembler,
            $this->recorder,
            $context,
        );
    }
}
