<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Song;

use Illuminate\Http\JsonResponse;
use OpenAPI\Client\Model\ErrorResponse;
use OpenAPI\Client\Model\SongGetResponse;
use ResultType\Result;
use Song\Application\UseCase\Get\GetOutputData;
use Support\UseCase\Error\AuthenticationError;
use Support\UseCase\Error\AuthorizationError;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\NotFoundError;
use Support\UseCase\Error\UseCaseError;

class GetPresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    /**
     * @param Result<GetOutputData, UseCaseError> $result
     */
    public function present(Result $result): JsonResponse
    {
        [$data, $status] = $result->match(
            function (GetOutputData $outputData) {
                return [
                    new SongGetResponse()->setSong($this->converter->toOpenApiSong($outputData->song)),
                    200,
                ];
            },
            function (UseCaseError $error) {
                return match (true) {
                    $error instanceof AuthenticationError => [[], 401],
                    $error instanceof AuthorizationError => [[], 403],
                    $error instanceof NotFoundError => [new ErrorResponse()->setMessage($this->resolveErrorMessage($error)), 404],
                    default => [
                        new ErrorResponse()->setMessage($this->resolveErrorMessage($error)),
                        400,
                    ],
                };
            },
        );

        return response()->json($data, $status);
    }

    private function resolveErrorMessage(UseCaseError $error): string
    {
        return match (true) {
            $error instanceof InvalidInputError => $this->firstMessage($error),
            $error instanceof NotFoundError => sprintf('%sが見つかりません: %s', $error->resourceName, $error->identifier),
            default => '予期しないエラーが発生しました',
        };
    }

    private function firstMessage(InvalidInputError $error): string
    {
        foreach ($error->errors as $messages) {
            if ($messages !== []) {
                return $messages[0];
            }
        }

        return '';
    }
}
