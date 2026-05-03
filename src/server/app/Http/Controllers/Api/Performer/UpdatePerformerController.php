<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Performer;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Performer\UpdatePresenter;
use Illuminate\Http\JsonResponse;
use Performer\Application\UseCase\Update\UpdateInputData;
use Performer\Application\UseCase\Update\UpdateUseCase;

class UpdatePerformerController extends Controller
{
    public function __construct(
        private readonly UpdateUseCase $useCase,
        private readonly UpdatePresenter $presenter,
    ) {
    }

    public function handle(UpdateInputData $inputData): JsonResponse
    {
        return $this->presenter->present($this->useCase->handle($inputData));
    }
}
