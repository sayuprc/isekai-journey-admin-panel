<?php

declare(strict_types=1);

use Arkitect\ClassSet;
use Arkitect\CLI\Config;
use Arkitect\RuleBuilders\Architecture\Architecture;

return static function (Config $config): void {
    $classSet = ClassSet::fromDir(
        __DIR__ . '/app',
        __DIR__ . '/packages'
    );

    $config->add(
        $classSet,
        ...Architecture::withComponents()
            ->component('JourneyLog.Domain')->definedBy('JourneyLog\Domain\*')
            ->component('JourneyLog.UseCase')->definedBy('JourneyLog\UseCases\*')

            ->component('JourneyLogLinkType.Domain')->definedBy('JourneyLogLinkType\Domain\*')
            ->component('JourneyLogLinkType.UseCase')->definedBy('JourneyLogLinkType\UseCases\*')

            ->component('Song.Domain')->definedBy('Song\Domain\*')
            ->component('Song.UseCase')->definedBy('Song\UseCases\*')

            ->component('SongType.Domain')->definedBy('SongType\Domain\*')

            ->component('Creator.Domain')->definedBy('Creator\Domain\*')
            ->component('Creator.UseCase')->definedBy('Creator\UseCases\*')

            ->component('Support.Domain')->definedBy('Support\Domain\*')
            ->component('Support.Result')->definedBy('Support\Result\*')

            ->where('JourneyLog.Domain')->shouldOnlyDependOnComponents('JourneyLog.Domain', 'JourneyLogLinkType.Domain', 'Support.Domain')
            ->where('JourneyLog.UseCase')->shouldOnlyDependOnComponents('JourneyLog.Domain', 'Support.Result')

            ->where('JourneyLogLinkType.Domain')->shouldOnlyDependOnComponents('JourneyLogLinkType.Domain', 'Support.Domain')
            ->where('JourneyLogLinkType.UseCase')->shouldOnlyDependOnComponents('JourneyLogLinkType.Domain', 'Support.Result')

            ->where('Song.Domain')->shouldOnlyDependOnComponents('Song.Domain', 'SongType.Domain', 'Creator.Domain', 'Support.Domain')
            ->where('Song.UseCase')->shouldOnlyDependOnComponents('Song.Domain')

            ->where('SongType.Domain')->shouldOnlyDependOnComponents('SongType.Domain', 'Support.Domain')

            ->where('Creator.Domain')->shouldOnlyDependOnComponents('Creator.Domain', 'Support.Domain')
            ->where('Creator.UseCase')->shouldOnlyDependOnComponents('Creator.Domain')

            ->where('Support.Domain')->shouldNotDependOnAnyComponent()

            ->rules()
    );
};
