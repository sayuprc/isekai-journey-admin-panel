<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\Place;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\Place\ListPresenter;
use Illuminate\Http\JsonResponse;
use Place\Application\Admin\UseCase\List\ListUseCase;

class ListPlaceController extends Controller
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
