<?php

declare(strict_types=1);

namespace App\Http\Requests\Creator;

use Creator\Application\UseCase\Create\CreateInputData;
use Tempest\Http\IsRequest;
use Tempest\Http\Request;

class CreateRequest implements Request
{
    use IsRequest;

    public function __construct(public string $creatorName)
    {
    }

    public function toInputData(): CreateInputData
    {
        return new CreateInputData($this->creatorName);
    }
}
