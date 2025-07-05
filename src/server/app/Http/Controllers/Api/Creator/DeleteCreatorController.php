<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Creator;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Creator\DeletePresenter;
use Creator\Application\UseCase\Delete\DeleteInputData;
use Creator\Application\UseCase\Delete\DeleteUseCaseInterface;
use Illuminate\Http\JsonResponse;

class DeleteCreatorController extends Controller
{
    public function __construct(
        private readonly DeleteUseCaseInterface $interactor,
        private readonly DeletePresenter $presenter,
    ) {
    }

    public function handle(string $creatorId): JsonResponse
    {
        $this->interactor->handle(new DeleteInputData($creatorId));

        return $this->presenter->present();
    }
}
