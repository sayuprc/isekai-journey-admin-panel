<?php

declare(strict_types=1);

namespace Place\Application\Admin\UseCase\Delete;

use AdminUser\Domain\Models\Permission;
use Place\Domain\Models\PlaceId;
use Place\Domain\Models\PlaceRepositoryInterface;
use Place\Domain\Services\PlaceUsageCheckerInterface;
use Support\Contracts\TransactionInterface;
use Support\Domain\Exceptions\BusinessRuleViolationException;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditLogRecorderInterface;
use Support\UseCase\AuditLog\AuditTargetType;
use Support\UseCase\Authorizer\UseCaseAuthorizer;
use Support\UseCase\Exceptions\ResourceNotFoundException;

readonly class DeleteUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private TransactionInterface $transaction,
        private PlaceRepositoryInterface $repository,
        private PlaceUsageCheckerInterface $usageChecker,
        private AuditLogRecorderInterface $recorder,
    ) {
    }

    public function handle(DeleteInputData $inputData): void
    {
        $this->authorizer->authorize(Permission::WritePlace);

        $placeId = new PlaceId($inputData->placeId);

        $this->transaction->scope(function () use ($placeId): void {
            $place = $this->repository->find($placeId);

            if (is_null($place)) {
                throw new ResourceNotFoundException('Place', $placeId->value);
            }

            if ($this->usageChecker->isUsed($placeId)) {
                throw new BusinessRuleViolationException('この場所は出来事に使用されているため削除できません');
            }

            $this->repository->delete($placeId);

            $this->recorder->record(
                AuditAction::Delete,
                AuditTargetType::Place,
                $place->placeId,
                $place->toArray(),
            );
        });
    }
}
