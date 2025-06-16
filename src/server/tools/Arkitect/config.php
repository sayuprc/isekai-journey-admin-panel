<?php

declare(strict_types=1);

use Tools\Arkitect\ComponentMaps\CreatorComponent;
use Tools\Arkitect\ComponentMaps\JourneyLogComponent;
use Tools\Arkitect\ComponentMaps\JourneyLogLinkTypeComponent;
use Tools\Arkitect\ComponentMaps\LibraryComponent;
use Tools\Arkitect\ComponentMaps\SongComponent;
use Tools\Arkitect\ComponentMaps\SongTypeComponent;
use Tools\Arkitect\ComponentMaps\SupportComponent;
use Tools\Arkitect\Define;

return [
    new Define(LibraryComponent::DateType),
    new Define(LibraryComponent::ResultType),

    new Define(
        JourneyLogComponent::Domain,
        [
            JourneyLogComponent::Domain,
            JourneyLogLinkTypeComponent::Domain,
            SupportComponent::Domain,
            LibraryComponent::DateType,
        ]
    ),
    new Define(
        JourneyLogComponent::UseCase,
        [
            JourneyLogComponent::Domain,
            LibraryComponent::DateType,
            LibraryComponent::ResultType,
        ]
    ),

    new Define(
        JourneyLogLinkTypeComponent::Domain,
        [
            JourneyLogLinkTypeComponent::Domain,
            SupportComponent::Domain,
        ]
    ),
    new Define(
        JourneyLogLinkTypeComponent::UseCase,
        [
            JourneyLogLinkTypeComponent::Domain,
            LibraryComponent::ResultType,
        ]
    ),

    new Define(
        SongComponent::Domain,
        [
            SongComponent::Domain,
            SongTypeComponent::Domain,
            CreatorComponent::Domain,
            SupportComponent::Domain,
            LibraryComponent::DateType,
        ]
    ),
    new Define(
        SongComponent::UseCase,
        [
            SongComponent::Domain,
            LibraryComponent::DateType,
        ]
    ),

    new Define(
        SongTypeComponent::Domain,
        [
            SongTypeComponent::Domain,
            SupportComponent::Domain,
        ]
    ),
    new Define(
        SongTypeComponent::UseCase,
        [
            SongTypeComponent::Domain,
            LibraryComponent::ResultType,
        ]
    ),

    new Define(
        CreatorComponent::Domain,
        [
            CreatorComponent::Domain,
            SupportComponent::Domain,
        ]
    ),
    new Define(
        CreatorComponent::UseCase,
        [
            CreatorComponent::Domain,
            LibraryComponent::ResultType,
        ]
    ),

    new Define(
        SupportComponent::Domain,
        [
            SupportComponent::Domain,
            LibraryComponent::DateType,
        ]
    ),
];
