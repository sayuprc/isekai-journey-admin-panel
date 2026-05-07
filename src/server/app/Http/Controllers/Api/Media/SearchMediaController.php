<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Media;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Media\SearchPresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Media\Application\UseCase\Search\SearchInputData;
use Media\Application\UseCase\Search\SearchUseCase;
use Support\Contracts\MapperInterface;

class SearchMediaController extends Controller
{
    public function __construct(
        private readonly MapperInterface $mapper,
        private readonly SearchUseCase $useCase,
        private readonly SearchPresenter $presenter,
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        return $this->mapper->map(SearchInputData::class, $request->query())
            |> $this->useCase->handle(...)
            |> $this->presenter->present(...);
    }
}
