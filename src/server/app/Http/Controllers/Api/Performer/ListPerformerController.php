<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Performer;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Performer\ListPresenter;
use Illuminate\Http\JsonResponse;
use Performer\Application\UseCase\List\ListUseCase;

class ListPerformerController extends Controller
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
