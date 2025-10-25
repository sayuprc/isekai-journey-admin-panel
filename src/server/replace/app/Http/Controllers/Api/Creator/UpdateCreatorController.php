<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Creator;

use App\Http\Middleware\Group\Creator;
use App\Http\Presenters\Api\Creator\UpdatePresenter;
use App\Http\Requests\Creator\UpdateRequest;
use App\Http\Responses\JsonResponse;
use Creator\Application\UseCase\Update\UpdateUseCaseInterface;
use Tempest\Http\Method;

class UpdateCreatorController
{
    public function __construct(
        private readonly UpdateUseCaseInterface $interactor,
        private readonly UpdatePresenter $presenter
    ) {
    }

    #[Creator(Method::PUT, '/{creatorId}')]
    public function handle(UpdateRequest $request): JsonResponse
    {
        return $this->presenter->present($this->interactor->handle($request->toInputData()));
    }
}
