<?php

declare(strict_types=1);

namespace App\Http\Requests\SongType;

use SongType\Application\UseCase\Create\CreateInputData;
use Tempest\Http\IsRequest;
use Tempest\Http\Request;

class CreateRequest implements Request
{
    use IsRequest;

    /**
     * @param positive-int $orderNo
     */
    public function __construct(public string $songTypeName, public int $orderNo)
    {
    }

    public function toInputData(): CreateInputData
    {
        return new CreateInputData($this->songTypeName, $this->orderNo);
    }
}
