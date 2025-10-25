<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Creator;

use App\Http\Middleware\Group\Creator;
use App\Http\Presenters\Api\Creator\GetPresenter;
use App\Http\Responses\JsonResponse;
use Creator\Application\UseCase\Get\GetInputData;
use Creator\Application\UseCase\Get\GetUseCaseInterface;
use Tempest\Http\Method;

class GetCreatorController
{
    public function __construct(
        private readonly GetUseCaseInterface $interactor,
        private readonly GetPresenter $presenter,
    ) {
    }

    #[Creator(Method::GET, '/{creatorId}')]
    public function handle(string $creatorId): JsonResponse
    {
        return $this->presenter->present($this->interactor->handle(new GetInputData($creatorId)));
    }
}
