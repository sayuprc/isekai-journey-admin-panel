<?php

declare(strict_types=1);

namespace Place\Application\Admin\UseCase\Get;

use AdminUser\Domain\Models\Permission;
use Place\Domain\Models\PlaceId;
use Place\Domain\Models\PlaceRepositoryInterface;
use Support\UseCase\Authorizer\UseCaseAuthorizer;
use Support\UseCase\Exceptions\ResourceNotFoundException;

readonly class GetUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private PlaceRepositoryInterface $repository,
    ) {
    }

    public function handle(GetInputData $inputData): GetOutputData
    {
        $this->authorizer->authorize(Permission::ReadPlace);

        $placeId = new PlaceId($inputData->placeId);

        if (is_null($found = $this->repository->find($placeId))) {
            throw new ResourceNotFoundException('Place', $placeId->value);
        }

        return new GetOutputData($found);
    }
}
