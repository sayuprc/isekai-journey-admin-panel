<?php

declare(strict_types=1);

namespace App\Features\JourneyLogLinkType\Adapter\Web\Presenters;

use App\Features\JourneyLogLinkType\Domain\Entities\JourneyLogLinkType;
use Exception;

/**
 * @property string $journeyLogLinkTypeId
 * @property string $journeyLogLinkTypeName
 * @property int    $orderNo
 */
class ListViewJourneyLogLinkType
{
    public function __construct(private readonly JourneyLogLinkType $journeyLogLinkType)
    {
    }

    /**
     * @throws Exception
     */
    public function __get(string $name): int|string
    {
        if (in_array($name, ['journeyLogLinkTypeId', 'journeyLogLinkTypeName', 'orderNo'], true)) {
            return $this->journeyLogLinkType->{$name}->value;
        }

        throw new Exception('Invalid property: ' . $name);
    }
}
