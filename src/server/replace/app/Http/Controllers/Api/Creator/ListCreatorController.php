<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Creator;

use App\Http\Middleware\Group\Creator;
use App\Http\Presenters\Api\Creator\ListPresenter;
use App\Http\Responses\JsonResponse;
use Creator\Application\UseCase\List\ListUseCaseInterface;
use Tempest\Http\Method;

class ListCreatorController
{
    public function __construct(
        private readonly ListUseCaseInterface $interactor,
        private readonly ListPresenter $presenter,
    ) {
    }

    #[Creator(Method::GET, '/')]
    public function handle(): JsonResponse
    {
        return $this->presenter->present($this->interactor->handle());
    }
}
