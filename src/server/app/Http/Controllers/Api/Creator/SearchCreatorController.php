<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Creator;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Creator\SearchPresenter;
use Creator\Application\UseCase\Search\SearchInputData;
use Creator\Application\UseCase\Search\SearchUseCaseInterface;
use Illuminate\Http\JsonResponse;

class SearchCreatorController extends Controller
{
    public function __construct(
        private readonly SearchUseCaseInterface $interactor,
        private readonly SearchPresenter $presenter,
    ) {
    }

    public function handle(SearchInputData $inputData): JsonResponse
    {
        return $this->presenter->present($this->interactor->handle($inputData));
    }
}
