<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\SongType;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\SongType\SongTypeCreatePresenter;
use Exception;
use OpenAPI\Client\Model\CreateSongTypeResponse;
use SongType\Application\UseCase\Create\CreateInputData;
use SongType\Application\UseCase\Create\CreateUseCaseInterface;

class CreateSongTypeController extends Controller
{
    public function handle(
        CreateInputData $inputData,
        CreateUseCaseInterface $interactor,
        SongTypeCreatePresenter $presenter
    ): CreateSongTypeResponse {
        $result = $interactor->handle($inputData);

        if ($result->isErr()) {
            // TODO エラーハンドリング
            throw new Exception($result->unwrapErr());
        }

        return $presenter->present($result->unwrap());
    }
}
