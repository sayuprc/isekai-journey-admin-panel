<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\AdminUser;

use AdminUser\Application\UseCase\List\ListUseCase;
use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\AdminUser\ListPresenter;
use Illuminate\Http\JsonResponse;

class ListAdminUserController extends Controller
{
    public function __construct(
        private readonly ListUseCase $useCase,
        private readonly ListPresenter $presenter,
    ) {
    }

    public function handle(): JsonResponse
    {
        return $this->presenter->present($this->useCase->handle());
    }
}
