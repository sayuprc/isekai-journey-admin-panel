<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Viewer\V1\Media;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Viewer\V1\Media\GetPresenter;
use Illuminate\Http\JsonResponse;
use Media\Application\Viewer\UseCase\Get\GetInputData;
use Media\Application\Viewer\UseCase\Get\GetUseCase;

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
