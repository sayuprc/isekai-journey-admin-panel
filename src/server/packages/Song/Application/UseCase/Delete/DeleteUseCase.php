<?php

declare(strict_types=1);

namespace Song\Application\UseCase\Delete;

use AdminUser\Domain\Models\Permission;
use Auth\Domain\Models\AuthContext;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Song\Application\Assemble\AssembledSong;
use Song\Application\Assemble\SongAssembler;
use Song\Domain\Models\SongId;
use Song\Domain\Models\SongRepositoryInterface;
use Support\Contracts\AuditLog\AuditAction;
use Support\Contracts\AuditLog\AuditLogRecorderInterface;
use Support\Contracts\AuditLog\AuditTargetType;
use Support\Contracts\TransactionInterface;
use Support\UseCase\Authorizer\UseCaseAuthorizer;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\NotFoundError;
use Support\UseCase\Error\UseCaseError;

readonly class DeleteUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private TransactionInterface $transaction,
        private SongRepositoryInterface $repository,
        private SongAssembler $assembler,
        private AuditLogRecorderInterface $recorder,
        private AuthContext $authContext,
    ) {
    }

    /**
     * @return Result<null, UseCaseError>
     */
    public function handle(DeleteInputData $inputData): Result
    {
        return $this->authorizer->require(Permission::WriteSong)
            ->andThen(fn () => $this->deleteSong($inputData));
    }

    /**
     * @return Result<null, UseCaseError>
     */
    private function deleteSong(DeleteInputData $inputData): Result
    {
        return SongId::create($inputData->songId)
            ->mapErr(fn (): UseCaseError => new InvalidInputError(['songId' => ['IDが不正です']]))
            ->andThen(fn (SongId $songId): Result => $this->transaction->scope(function () use ($songId): Result {
                $song = $this->repository->find($songId);

                if (is_null($song)) {
                    return new Err(new NotFoundError('Song', $songId->value));
                }

                $assembled = $this->assembler->assemble($song);

                $this->repository->delete($songId);

                $actor = $this->authContext->get();
                assert(! is_null($actor));

                $this->recorder->record(
                    $actor->adminUserId->value,
                    AuditAction::Delete,
                    AuditTargetType::Song,
                    $assembled->songId,
                    $this->snapshot($assembled),
                );

                return new Ok(null);
            }));
    }

    /**
     * @return array<string, mixed>
     */
    private function snapshot(AssembledSong $assembled): array
    {
        return [
            'songId' => $assembled->songId,
            'title' => $assembled->title,
            'description' => $assembled->description,
            'lyricsLink' => $assembled->lyricsLink,
            'typeName' => $assembled->typeName,
            'typeValue' => $assembled->typeValue,
            'isDisplay' => $assembled->isDisplay,
            'orderNo' => $assembled->orderNo,
            'tagIds' => array_map(fn ($t) => $t->songTagId, $assembled->tags),
            'persons' => array_map(
                fn ($p) => [
                    'personId' => $p->personId,
                    'role' => $p->role->value,
                    'orderNo' => $p->orderNo,
                ],
                $assembled->persons,
            ),
        ];
    }
}
