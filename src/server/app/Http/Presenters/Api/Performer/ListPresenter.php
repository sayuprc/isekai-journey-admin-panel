<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Performer;

use Illuminate\Http\JsonResponse;
use OpenAPI\Client\Model\PerformerListResponse;
use Performer\Application\UseCase\List\ListOutputData;
use Performer\Domain\Models\Performer;

class ListPresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    public function present(ListOutputData $outputData): JsonResponse
    {
        return response()->json(
            new PerformerListResponse()
                ->setPerformers(
                    array_map(
                        fn (Performer $performer) => $this->converter->toOpenApiPerformer($performer),
                        $outputData->performers,
                    ),
                ),
            200,
        );
    }
}
