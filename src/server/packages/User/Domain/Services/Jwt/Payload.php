<?php

declare(strict_types=1);

namespace User\Domain\Services\Jwt;

interface Payload
{
    /**
     * @return array<mixed>
     */
    public function toArray(): array;
}
