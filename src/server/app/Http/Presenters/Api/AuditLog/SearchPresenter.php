<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\AuditLog;

use App\Http\Presenters\Api\Support\ResolvesUseCaseError;
use Illuminate\Http\JsonResponse;
use OpenAPI\Client\Model\AuditLogSearchResponse;
use ResultType\Result;
use Support\UseCase\AuditLog\Search\SearchOutputData;
use Support\UseCase\Error\UseCaseError;

class SearchPresenter
{
    use ResolvesUseCaseError;

    public function __construct(private readonly Converter $converter)
    {
    }

    /**
     * @param Result<SearchOutputData, UseCaseError> $result
     */
    public function present(Result $result): JsonResponse
    {
        [$data, $status] = $result->match(
            fn (SearchOutputData $outputData) => [
                new AuditLogSearchResponse()
                    ->setAuditLogs(array_map($this->converter->toOpenApiSummary(...), $outputData->auditLogs))
                    ->setMaxPage($outputData->maxPage),
                200,
            ],
            fn (UseCaseError $error) => $this->resolveError($error),
        );

        return response()->json($data, $status);
    }
}
