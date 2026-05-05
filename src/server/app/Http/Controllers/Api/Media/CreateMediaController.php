<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Media;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Media\CreatePresenter;
use Illuminate\Http\JsonResponse;
use Media\Application\UseCase\Create\CreateInputData;
use Media\Application\UseCase\Create\CreateUseCase;

class CreateMediaController extends Controller
{
    public function __construct(
        private readonly CreateUseCase $useCase,
        private readonly CreatePresenter $presenter,
    ) {
    }

    public function handle(CreateInputData $inputData): JsonResponse
    {
        return $this->presenter->present($this->useCase->handle($inputData));
    }
}
