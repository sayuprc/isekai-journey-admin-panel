<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\SongTag;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\SongTag\SearchPresenter;
use Illuminate\Http\JsonResponse;
use Song\Application\UseCase\Tag\Search\SearchInputData;
use Song\Application\UseCase\Tag\Search\SearchUseCaseInterface;

class SearchSongTagController extends Controller
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
