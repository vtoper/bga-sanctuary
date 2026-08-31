<?php

namespace Bga\Games\Sanctuary\Tiles\Animals;

use Bga\Games\Sanctuary\Constants\Icons;
use Bga\Games\Sanctuary\Constants\Effects;

class A008_AustralianSeaLion_N extends \Bga\Games\Sanctuary\Models\Animal
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'A008_AustralianSeaLion_N';
    $this->name = 'AUSTRALIAN SEA LION';
    $this->appeal = '2 per predator';
    $this->strength = 4;
    $this->gender = 'N';
    $this->effect = [Effects::HUNTER => 3];

    $this->categories = [Icons::WATER, Icons::PREDATOR];
    $this->continents = [Icons::AUSTRALIA];
    $this->openAreas = ['N'];
  }
}
