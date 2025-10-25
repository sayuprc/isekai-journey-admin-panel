<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Creator;

use App\Http\Middleware\Group\Creator;
use App\Http\Presenters\Api\Creator\CreatePresenter;
use App\Http\Requests\Creator\CreateRequest;
use App\Http\Responses\JsonResponse;
use Creator\Application\UseCase\Create\CreateUseCaseInterface;
use Tempest\Http\Method;

class CreateCreatorController
{
    public function __construct(
        private readonly CreateUseCaseInterface $interactor,
        private readonly CreatePresenter $presenter
    ) {
    }

    #[Creator(Method::POST, '/')]
    public function handle(CreateRequest $request): JsonResponse
    {
        return $this->presenter->present($this->interactor->handle($request->toInputData()));
    }
}
