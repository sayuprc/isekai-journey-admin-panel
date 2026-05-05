<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Media;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Media\DeletePresenter;
use Illuminate\Http\JsonResponse;
use Media\Application\UseCase\Delete\DeleteInputData;
use Media\Application\UseCase\Delete\DeleteUseCase;

class DeleteMediaController extends Controller
{
    public function __construct(
        private readonly DeleteUseCase $useCase,
        private readonly DeletePresenter $presenter,
    ) {
    }

    public function handle(string $mediaId): JsonResponse
    {
        return $this->presenter->present($this->useCase->handle(new DeleteInputData($mediaId)));
    }
}
