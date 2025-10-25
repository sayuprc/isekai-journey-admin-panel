<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Creator;

use App\Http\Middleware\Group\Creator;
use App\Http\Presenters\Api\Creator\DeletePresenter;
use App\Http\Responses\JsonResponse;
use Creator\Application\UseCase\Delete\DeleteInputData;
use Creator\Application\UseCase\Delete\DeleteUseCaseInterface;
use Tempest\Http\Method;

class DeleteCreatorController
{
    public function __construct(
        private readonly DeleteUseCaseInterface $interactor,
        private readonly DeletePresenter $presenter,
    ) {
    }

    #[Creator(Method::DELETE, '/{creatorId}')]
    public function handle(string $creatorId): JsonResponse
    {
        $this->interactor->handle(new DeleteInputData($creatorId));

        return $this->presenter->present();
    }
}
