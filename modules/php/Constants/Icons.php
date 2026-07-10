<?php

declare(strict_types=1);

namespace Bga\Games\sanctuary\Constants;

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
    const PET = 'Pet';
    const PRIMATE = 'Primate';
    const ANIMAL_TYPES = [self::BIRD, self::PREDATOR, self::HERBIVORE, self::BEAR, self::REPTILE, self::PET, self::PRIMATE];

    const CONTINENTS_AND_TYPES = [self::AFRICA, self::EUROPE, self::ASIA, self::AMERICAS, self::AUSTRALIA, self::BIRD, self::PREDATOR, self::HERBIVORE, self::BEAR, self::REPTILE, self::PET, self::PRIMATE];

    // Habitat
    const ROCK = 'Rock';
    const WATER = 'Water';
    const FOREST = 'Forest';
    const HABITATS = [self::ROCK, self::WATER, self::FOREST];
}
