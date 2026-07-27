<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\SongTag;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\SongTag\ResetOrderNumbersPresenter;
use Illuminate\Http\JsonResponse;
use Song\Application\Admin\UseCase\Tag\ResetOrderNumbers\ResetOrderNumbersUseCase;

class ResetSongTagOrderNumbersController extends Controller
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
