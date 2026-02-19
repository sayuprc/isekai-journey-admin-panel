<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Song;

use Illuminate\Http\JsonResponse;
use OpenAPI\Client\Model\ErrorResponse;
use OpenAPI\Client\Model\SongCreateResponse;
use ResultType\Result;
use Song\Application\UseCase\Create\CreateOutputData;
use Support\UseCase\Error\AuthenticationError;
use Support\UseCase\Error\AuthorizationError;
use Support\UseCase\Error\InvalidInputError;
use Support\UseCase\Error\UseCaseError;

class CreatePresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    /**
     * @param Result<CreateOutputData, UseCaseError> $result
     */
    public function present(Result $result): JsonResponse
    {
        [$data, $status] = $result->match(
            function (CreateOutputData $outputData) {
                $song = $outputData->song;

                return [
                    new SongCreateResponse()->setSong($this->converter->toOpenApiSong($song)),
                    200,
                ];
            },
            function (UseCaseError $error) {
                return match (true) {
                    $error instanceof AuthenticationError => [[], 401],
                    $error instanceof AuthorizationError => [[], 403],
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
        if (! $error instanceof InvalidInputError) {
            return '';
        }

        foreach ($error->errors as $messages) {
            if ($messages !== []) {
                return $messages[0];
            }
        }

        return '';
    }
}
