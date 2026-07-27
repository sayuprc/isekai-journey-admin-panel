<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\Person;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\Person\ResetOrderNumbersPresenter;
use Illuminate\Http\JsonResponse;
use Person\Application\Admin\UseCase\ResetOrderNumbers\ResetOrderNumbersUseCase;

class ResetPersonOrderNumbersController extends Controller
{
    public function __construct(
        private readonly ResetOrderNumbersUseCase $useCase,
        private readonly ResetOrderNumbersPresenter $presenter,
    ) {
    }

    public function handle(): JsonResponse
    {
        return $this->presenter->present($this->useCase->handle());
    }
}
