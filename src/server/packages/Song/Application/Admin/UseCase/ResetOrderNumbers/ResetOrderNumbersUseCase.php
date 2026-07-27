<?php

declare(strict_types=1);

namespace Song\Application\Admin\UseCase\ResetOrderNumbers;

use AdminUser\Domain\Models\Permission;
use ResultType\Ok;
use ResultType\Result;
use Song\Domain\Models\SongId;
use Song\Domain\Models\SongRepositoryInterface;
use Support\Contracts\TransactionInterface;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditLogRecorderInterface;
use Support\UseCase\AuditLog\AuditTargetType;
use Support\UseCase\Authorizer\UseCaseAuthorizer;
use Support\UseCase\Error\UseCaseError;

readonly class ResetOrderNumbersUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private TransactionInterface $transaction,
        private SongRepositoryInterface $repository,
        private AuditLogRecorderInterface $recorder,
    ) {
    }

    /**
     * @return Result<ResetOrderNumbersOutputData, UseCaseError>
     */
    public function handle(): Result
    {
        return $this->authorizer->require(Permission::WriteSong)
            ->andThen(fn () => $this->reset());
    }

    /**
     * @return Result<ResetOrderNumbersOutputData, UseCaseError>
     */
    private function reset(): Result
    {
        return $this->transaction->scope(function (): Result {
            $updated = $this->repository->resetOrderNumbers();

            foreach ($updated as $row) {
                $song = $this->repository->find(SongId::reconstruct($row['id']));

                if (is_null($song)) {
                    continue;
                }

                $this->recorder->record(
                    AuditAction::Update,
                    AuditTargetType::Song,
                    $song->songId,
                    $song->toArray(),
                );
            }

            return new Ok(new ResetOrderNumbersOutputData(count($updated)));
        });
    }
}
