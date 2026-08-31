<?php

namespace Bga\Games\Sanctuary\Tiles\Animals;

use Bga\Games\Sanctuary\Constants\Icons;
use Bga\Games\Sanctuary\Constants\Effects;

class A059_JapaneseMacaque_N extends \Bga\Games\Sanctuary\Models\Animal
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'A059_JapaneseMacaque_N';
    $this->name = 'JAPANESE MACAQUE';
    $this->appeal = '6';
    $this->strength = 4;
    $this->gender = 'N';
    //effect = 'immediate draw 2 tiles from the pile';
    $this->effect = [Effects::DRAW_TILE => 1];

    $this->categories = [Icons::WATER, Icons::PRIMATE];
    $this->continents = [Icons::ASIA];
    $this->openAreas = ['NE'];
  }
}
