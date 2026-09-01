<?php

namespace Bga\Games\Sanctuary\Tiles\Animals;

use Bga\Games\Sanctuary\Constants\Icons;
use Bga\Games\Sanctuary\Constants\Effects;

class A039_Koala_F extends \Bga\Games\Sanctuary\Models\Animal
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'A039_Koala_F';
    $this->name = 'KOALA';
    $this->appeal = '5';
    $this->strength = 3;
    $this->gender = 'F';
    //effect = 'immediate discard up to 2 tiles, gain 1 pouch marker for each';
    $this->effect = [Effects::POUCH => 2];
    $this->categories = [Icons::FOREST, Icons::HERBIVORE];
    $this->continents = [Icons::AUSTRALIA];
    $this->pair = 'A038_Koala_M';
  }
}
