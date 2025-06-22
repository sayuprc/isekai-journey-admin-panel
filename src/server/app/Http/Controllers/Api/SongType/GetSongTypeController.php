<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\SongType;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\SongType\SongTypeGetPresenter;
use Illuminate\Http\JsonResponse;
use OpenAPI\Client\Model\GetSongTypeResponse;
use SongType\Application\UseCase\Get\GetInputData;
use SongType\Application\UseCase\Get\GetUseCaseInterface;

class GetSongTypeController extends Controller
{
    public function handle(
        string $songTypeId,
        GetUseCaseInterface $interactor,
        SongTypeGetPresenter $presenter
    ): GetSongTypeResponse|JsonResponse {
        $result = $interactor->handle(new GetInputData($songTypeId));

        if ($result->isErr()) {
            // TODO エラーハンドリングをちゃんとする
            return response()->json(status:404);
        }

        return $presenter->present($result->unwrap());
    }
}
