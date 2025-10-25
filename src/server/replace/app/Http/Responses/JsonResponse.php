<?php

declare(strict_types=1);

namespace App\Http\Responses;

use JsonSerializable;
use Tempest\Http\IsResponse;
use Tempest\Http\Response;
use Tempest\Http\Status;

class JsonResponse implements Response
{
    use IsResponse;

    /**
     * @param array<mixed>|JsonSerializable|null $body
     */
    public function __construct(null|array|JsonSerializable $body = null, int $status = 200)
    {
        $this->status = Status::from($status);
        $this->body = $body;
        $this->addHeader('Accept', 'application/json');
        $this->addHeader('Content-Type', 'application/json');
    }
}
