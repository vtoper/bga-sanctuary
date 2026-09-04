<?php

declare(strict_types=1);

namespace Bga\Games\Sanctuary\Constants;

class Icons
{
    // Continent
    const AFRICA = 'Africa';
    const EUROPE = 'Europe';
    const ASIA = 'Asia';
    const AMERICAS = 'Americas';
    const AUSTRALIA = 'Australia';
    const CONTINENTS = [self::AFRICA, self::EUROPE, self::ASIA, self::AMERICAS, self::AUSTRALIA];

    // Animal type
    const BIRD = 'Bird';
    const PREDATOR = 'Predator';
    const HERBIVORE = 'Herbivore';
    const BEAR = 'Bear';
    const REPTILE = 'Reptile';
    const PRIMATE = 'Primate';
    const PETTING_ZOO = 'PettingZoo';
    const ANIMAL_TYPES = [self::BIRD, self::PREDATOR, self::HERBIVORE,  self::REPTILE, self::PRIMATE, self::PETTING_ZOO];

    const CONTINENTS_AND_TYPES = [self::AFRICA, self::EUROPE, self::ASIA, self::AMERICAS, self::AUSTRALIA, self::BIRD, self::PREDATOR, self::HERBIVORE, self::BEAR, self::REPTILE, self::PRIMATE, self::PETTING_ZOO];

    // Habitat
    const ROCK = 'Rock';
    const WATER = 'Water';
    const FOREST = 'Forest';
    const UNDEFINED = 'Undefined';
    const HABITATS = [self::ROCK, self::WATER, self::FOREST, self::UNDEFINED];

    const CONTINENTS_AND_TYPES_AND_HABITATS = [self::AFRICA, self::EUROPE, self::ASIA, self::AMERICAS, self::AUSTRALIA, self::BIRD, self::PREDATOR, self::HERBIVORE, self::BEAR, self::REPTILE, self::PRIMATE, self::PETTING_ZOO, self::ROCK, self::WATER, self::FOREST, self::UNDEFINED];

    // Animal Size
    const SMALL_ANIMALS = 'SmallAnimals'; // 2-3
    const LARGE_ANIMALS = 'LargeAnimals'; // 4-5
}
