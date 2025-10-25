<?php

declare(strict_types=1);

namespace App\Http\Requests\SongType;

use SongType\Application\UseCase\Update\UpdateInputData;
use Tempest\Http\IsRequest;
use Tempest\Http\Request;

class UpdateRequest implements Request
{
    use IsRequest;

    /**
     * @param positive-int $orderNo
     */
    public function __construct(
        public string $songTypeId,
        public string $songTypeName,
        public int $orderNo,
    ) {
    }

    public function toInputData(): UpdateInputData
    {
        return new UpdateInputData($this->songTypeId, $this->songTypeName, $this->orderNo);
    }
}
