<?php

declare(strict_types=1);

namespace App\Http\Requests\Creator;

use Creator\Application\UseCase\Update\UpdateInputData;
use Tempest\Http\IsRequest;
use Tempest\Http\Request;

class UpdateRequest implements Request
{
    use IsRequest;

    public function __construct(
        public string $creatorId,
        public string $creatorName
    ) {
    }

    public function toInputData(): UpdateInputData
    {
        return new UpdateInputData($this->creatorId, $this->creatorName);
    }
}
