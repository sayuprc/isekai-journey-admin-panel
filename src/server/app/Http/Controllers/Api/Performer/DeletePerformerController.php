<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Performer;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Performer\DeletePresenter;
use Illuminate\Http\JsonResponse;
use Performer\Application\UseCase\Delete\DeleteInputData;
use Performer\Application\UseCase\Delete\DeleteUseCaseInterface;

class DeletePerformerController extends Controller
{
    public function __construct(
        private readonly DeleteUseCaseInterface $interactor,
        private readonly DeletePresenter $presenter,
    ) {
    }

    public function handle(string $performerId): JsonResponse
    {
        return $this->presenter->present($this->interactor->handle(new DeleteInputData($performerId)));
    }
}
