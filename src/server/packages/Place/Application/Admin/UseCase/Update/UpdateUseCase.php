<?php

declare(strict_types=1);

namespace Place\Application\Admin\UseCase\Update;

use AdminUser\Domain\Models\Permission;
use Place\Domain\Models\PlaceId;
use Place\Domain\Models\PlaceRepositoryInterface;
use Place\Domain\Services\PlaceIntegrityService;
use Support\Contracts\TransactionInterface;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditLogRecorderInterface;
use Support\UseCase\AuditLog\AuditTargetType;
use Support\UseCase\Authorizer\UseCaseAuthorizer;
use Support\UseCase\Exceptions\ResourceNotFoundException;

readonly class UpdateUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private TransactionInterface $transaction,
        private PlaceRepositoryInterface $repository,
        private PlaceIntegrityService $service,
        private AuditLogRecorderInterface $recorder,
    ) {
    }

    public function handle(UpdateInputData $inputData): UpdateOutputData
    {
        $this->authorizer->authorize(Permission::WritePlace);

        $placeId = new PlaceId($inputData->placeId);

        return $this->transaction->scope(function () use ($inputData, $placeId): UpdateOutputData {
            if (is_null($this->repository->find($placeId))) {
                throw new ResourceNotFoundException('Place', $placeId->value);
            }

            $place = $this->service->prepareForUpdate($inputData->placeId, $inputData->name, $inputData->kindValue);

            $this->repository->save($place);

            $this->recorder->record(
                AuditAction::Update,
                AuditTargetType::Place,
                $place->placeId,
                $place->toArray(),
            );

            return new UpdateOutputData($place);
        });
    }
}
