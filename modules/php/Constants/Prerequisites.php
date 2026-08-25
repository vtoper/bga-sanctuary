<?php

declare(strict_types=1);

namespace Bga\Games\Sanctuary\Constants;

class Prerequisites
{
    // Icon keys (ex: Icons::WATER => 2) are checked directly against the neighbours' icons

    // Prefix used for "next to N connected <icon> tiles" (ex: 'CONNECT_' . Icons::FOREST => 4)
    const CONNECT_PREFIX = 'CONNECT_';

    // Neighbour tile-type counts
    const NEXT_TO_BUILDINGS = 'NextToBuildings';
    const NEXT_TO_PROJECTS = 'NextToProjects';
    const NEXT_TO_OPEN_AREAS = 'NextToOpenAreas';
    const NEXT_TO_LARGE_ANIMALS = 'NextToLargeAnimals';
    const NEXT_TO_DIFFERENT_ANIMAL_OR_CONTINENT_ICONS = 'NextToDifferentAnimalOrContinentIcons';

    // Position based (not dependent on neighbours)
    const BY_THE_RIVER = 'ByTheRiver';

    // Player/zoo-wide checks (not dependent on the placement position)
    const HAVE_DIFFERENT_CONTINENT_ICONS = 'HaveDifferentContinentIcons';
    const HAVE_DIFFERENT_ANIMAL_ICONS = 'HaveDifferentAnimalIcons';
    const HAVE_DIFFERENT_ANIMAL_AND_CONTINENT_ICONS = 'HaveDifferentAnimalAndContinentIcons';
    const HAVE_ALL_RIVER_SPACES_FILLED = 'HaveAllRiverSpacesFilled';
    const HAVE_TILES_IN_HAND = 'HaveTilesInHand';

    // Strength (level) threshold from which an animal is considered "large"
    const LARGE_ANIMAL_STRENGTH = 4;
}
