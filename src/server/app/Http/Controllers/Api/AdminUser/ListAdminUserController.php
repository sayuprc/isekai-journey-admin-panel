<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\AdminUser;

use AdminUser\Application\UseCase\List\ListInputData;
use AdminUser\Application\UseCase\List\ListUseCaseInterface;
use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\AdminUser\ListPresenter;
use Illuminate\Http\JsonResponse;

class ListAdminUserController extends Controller
{
    public function __construct(
        private readonly ListUseCaseInterface $interactor,
        private readonly ListPresenter $presenter,
    ) {
    }

    public function handle(): JsonResponse
    {
        return $this->presenter->present($this->interactor->handle(new ListInputData()));
    }
}
