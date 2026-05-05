<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Media;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Media\SearchPresenter;
use Illuminate\Http\JsonResponse;
use Media\Application\UseCase\Search\SearchInputData;
use Media\Application\UseCase\Search\SearchUseCase;

class SearchMediaController extends Controller
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
