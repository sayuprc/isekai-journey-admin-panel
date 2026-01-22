<?php

declare(strict_types=1);

use Tools\Arkitect\ComponentMaps\CreatorComponent;
use Tools\Arkitect\ComponentMaps\LibraryComponent;
use Tools\Arkitect\ComponentMaps\PerformerComponent;
use Tools\Arkitect\ComponentMaps\SongTypeComponent;
use Tools\Arkitect\ComponentMaps\SupportComponent;
use Tools\Arkitect\Define;

return [
    new Define(LibraryComponent::DateType),
    new Define(LibraryComponent::ResultType),

    new Define(
        SongTypeComponent::Domain,
        [
            SongTypeComponent::Domain,
            SupportComponent::Domain,
        ],
    ),
    new Define(
        SongTypeComponent::UseCase,
        [
            SongTypeComponent::Domain,
            LibraryComponent::ResultType,
        ],
    ),

    new Define(
        CreatorComponent::Domain,
        [
            CreatorComponent::Domain,
            SupportComponent::Domain,
        ],
    ),
    new Define(
        CreatorComponent::UseCase,
        [
            CreatorComponent::Domain,
            LibraryComponent::ResultType,
        ],
    ),

    new Define(
        PerformerComponent::Domain,
        [
            PerformerComponent::Domain,
            SupportComponent::Domain,
        ],
    ),
    new Define(
        PerformerComponent::UseCase,
        [
            PerformerComponent::Domain,
            LibraryComponent::ResultType,
        ],
    ),

    new Define(
        SupportComponent::Domain,
        [
            SupportComponent::Domain,
            LibraryComponent::DateType,
        ],
    ),
];
