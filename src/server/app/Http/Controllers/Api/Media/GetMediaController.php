<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Media;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Media\GetPresenter;
use Illuminate\Http\JsonResponse;
use Media\Application\UseCase\Get\GetInputData;
use Media\Application\UseCase\Get\GetUseCase;

class GetMediaController extends Controller
{
    public function __construct(
        private readonly GetUseCase $useCase,
        private readonly GetPresenter $presenter,
    ) {
    }

    public function handle(string $mediaId): JsonResponse
    {
        return $this->presenter->present($this->useCase->handle(new GetInputData($mediaId)));
    }
}
