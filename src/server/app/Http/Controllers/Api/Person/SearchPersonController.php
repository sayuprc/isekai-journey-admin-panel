<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Person;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Person\SearchPresenter;
use Illuminate\Http\JsonResponse;
use Person\Application\UseCase\Search\SearchInputData;
use Person\Application\UseCase\Search\SearchUseCase;

class SearchPersonController extends Controller
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
