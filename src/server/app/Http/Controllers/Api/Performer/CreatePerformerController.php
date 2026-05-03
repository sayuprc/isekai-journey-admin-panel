<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Performer;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Performer\CreatePresenter;
use Illuminate\Http\JsonResponse;
use Performer\Application\UseCase\Create\CreateInputData;
use Performer\Application\UseCase\Create\CreateUseCase;

class CreatePerformerController extends Controller
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
