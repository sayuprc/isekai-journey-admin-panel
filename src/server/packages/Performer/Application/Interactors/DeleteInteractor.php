<?php

declare(strict_types=1);

namespace Performer\Application\Interactors;

use Override;
use AdminUser\Domain\Models\Permission;
use Auth\Domain\Models\AuthContext;
use Performer\Application\UseCase\Delete\DeleteInputData;
use Performer\Application\UseCase\Delete\DeleteUseCaseInterface;
use Performer\Domain\Models\PerformerId;
use Performer\Domain\Models\PerformerRepositoryInterface;
use ResultType\Err;
use ResultType\Ok;
use ResultType\Result;
use Support\UseCase\Error\AuthenticationError;
use Support\UseCase\Error\AuthorizationError;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\UseCaseError;

readonly class DeleteInteractor implements DeleteUseCaseInterface
{
    public function __construct(
        private AuthContext $context,
        private PerformerRepositoryInterface $repository,
    ) {
    }

    #[Override]
    public function handle(DeleteInputData $inputData): Result
    {
        $user = $this->context->get();

        if (is_null($user)) {
            return new Err(new AuthenticationError());
        }

        if (! $user->can(Permission::WritePerformer)) {
            return new Err(new AuthorizationError());
        }

        return PerformerId::create($inputData->performerId)
            ->mapErr(fn (): UseCaseError => new InvalidInputError(['performerId' => ['IDが不正です']]))
            ->andThen(function (PerformerId $performerId): Result {
                $this->repository->delete($performerId);

                return new Ok(null);
            });
    }
}
