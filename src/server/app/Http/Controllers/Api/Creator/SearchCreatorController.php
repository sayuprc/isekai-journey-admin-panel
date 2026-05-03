<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Creator;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Creator\SearchPresenter;
use Creator\Application\UseCase\Search\SearchInputData;
use Creator\Application\UseCase\Search\SearchUseCase;
use Illuminate\Http\JsonResponse;

class SearchCreatorController extends Controller
{
    public function __construct(
        private readonly SearchUseCase $useCase,
        private readonly SearchPresenter $presenter,
    ) {
    }

    public function handle(SearchInputData $inputData): JsonResponse
    {
        return $this->presenter->present($this->useCase->handle($inputData));
    }
}
