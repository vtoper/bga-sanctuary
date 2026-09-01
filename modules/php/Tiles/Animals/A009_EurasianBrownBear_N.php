<?php

namespace Bga\Games\Sanctuary\Tiles\Animals;

use Bga\Games\Sanctuary\Constants\Icons;
use Bga\Games\Sanctuary\Constants\Effects;
use Bga\Games\Sanctuary\Models\Tile;

class A009_EurasianBrownBear_N extends \Bga\Games\Sanctuary\Models\Animal
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'A009_EurasianBrownBear_N';
    $this->name = 'EURASIAN BROWN BEAR';
    $this->appeal = '8';
    $this->strength = 5;
    $this->gender = 'N';
    $this->effect = [Effects::TAKE_ALL_TILES => Tile::TILE_PROJECT];
    $this->categories = [Icons::FOREST, Icons::PREDATOR];
    $this->continents = [Icons::EUROPE];
    $this->openAreas = ['N'];
  }
}
