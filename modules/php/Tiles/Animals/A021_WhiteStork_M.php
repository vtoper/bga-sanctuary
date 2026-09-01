<?php

namespace Bga\Games\Sanctuary\Tiles\Animals;

use Bga\Games\Sanctuary\Constants\Icons;
use Bga\Games\Sanctuary\Constants\Effects;

class A021_WhiteStork_M extends \Bga\Games\Sanctuary\Models\Animal
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'A021_WhiteStork_M';
    $this->name = 'WHITE STORK';
    $this->appeal = '1 per open area';
    $this->strength = 3;
    $this->gender = 'M';
    //effect = 'immediate place 1 open area from the pile in your zoo';
    $this->effect = [Effects::PLACE_OPEN_AREAS => 1];
    $this->categories = [Icons::FOREST, Icons::BIRD];
    $this->continents = [Icons::EUROPE];
    $this->pair = 'A020_WhiteStork_F';
  }
}
