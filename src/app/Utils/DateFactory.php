<?php

declare(strict_types=1);

namespace App\Utils;

use DateTimeImmutable;
use DateTimeInterface;
use Generated\IsekaiJourney\JourneyLog\Date;

class DateFactory
{
    public static function fromString(string $value): Date
    {
        return self::fromDateTimeInterface(new DateTimeImmutable($value));
    }

    public static function fromDateTimeInterface(DateTimeInterface $value): Date
    {
        $date = new Date();

        return $date->setYear($value->format('Y'))
            ->setMonth($value->format('m'))
            ->setDay($value->format('d'));
    }
}
