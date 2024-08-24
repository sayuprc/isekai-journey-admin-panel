<?php

declare(strict_types=1);

namespace App\Http\ViewModel;

class ViewJourneyLog
{
    public function __construct(
        public readonly string $journeyLogId,
        public readonly string $summary,
        public readonly string $story,
        private readonly ViewDate $fromOn,
        private readonly ViewDate $toOn,
    ) {
    }

    public function period(): string
    {
        return $this->fromOn->equal($this->toOn)
            ? $this->fromOn->format()
            : $this->fromOn->format() . ' ~ ' . $this->toOn->format();
    }
}
