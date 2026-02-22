<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Performer;

use OpenAPI\Client\Model\Performer as OpenApiPerformer;
use Performer\Domain\Models\Performer;

class Converter
{
    public function toOpenApiPerformer(Performer $performer): OpenApiPerformer
    {
        return new OpenApiPerformer()
            ->setPerformerId($performer->performerId->value)
            ->setName($performer->name->value)
            ->setOrderNo($performer->orderNo->value);
    }
}
