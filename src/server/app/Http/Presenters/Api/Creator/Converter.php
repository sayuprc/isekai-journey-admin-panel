<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Creator;

use Creator\Domain\Models\Creator;
use OpenAPI\Client\Model\Creator as OpenApiCreator;

class Converter
{
    public function toOpenApiCreator(Creator $creator): OpenApiCreator
    {
        return new OpenApiCreator()
            ->setCreatorId($creator->creatorId->value)
            ->setCreatorName($creator->creatorName->value);
    }
}
