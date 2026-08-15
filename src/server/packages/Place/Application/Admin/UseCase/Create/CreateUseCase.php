<?php

declare(strict_types=1);

namespace Place\Application\Admin\UseCase\Create;

use AdminUser\Domain\Models\Permission;
use Place\Domain\Models\PlaceRepositoryInterface;
use Place\Domain\Services\PlaceIntegrityService;
use Support\Contracts\TransactionInterface;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditLogRecorderInterface;
use Support\UseCase\AuditLog\AuditTargetType;
use Support\UseCase\Authorizer\UseCaseAuthorizer;

readonly class CreateUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private TransactionInterface $transaction,
        private PlaceRepositoryInterface $repository,
        private PlaceIntegrityService $service,
        private AuditLogRecorderInterface $recorder,
    ) {
    }

    public function handle(CreateInputData $inputData): CreateOutputData
    {
        $this->authorizer->authorize(Permission::WritePlace);

        return $this->transaction->scope(function () use ($inputData): CreateOutputData {
            $place = $this->service->prepareForCreate($inputData->name, $inputData->kindValue);

            $this->repository->save($place);

            $this->recorder->record(
                AuditAction::Create,
                AuditTargetType::Place,
                $place->placeId,
                $place->toArray(),
            );

            return new CreateOutputData($place);
        });
    }
}
