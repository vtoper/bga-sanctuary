<?php

namespace Bga\Games\Sanctuary\Tiles\Animals;

use Bga\Games\Sanctuary\Constants\Icons;
use Bga\Games\Sanctuary\Constants\Effects;

class A020_WhiteStork_F extends \Bga\Games\Sanctuary\Models\Animal
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'A020_WhiteStork_F';
    $this->name = 'WHITE STORK';
    $this->appeal = '1 per open area';
    $this->strength = 3;
    $this->gender = 'F';
    $this->effect = [Effects::PLACE_OPEN_AREAS => 1];
    //effect = 'immediate place 1 open area from the pile in your zoo';
    $this->categories = [Icons::FOREST, Icons::BIRD];
    $this->continents = [Icons::EUROPE];
    $this->pair = 'A021_WhiteStork_M';
  }
}
