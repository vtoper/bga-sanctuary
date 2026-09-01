<?php

namespace Bga\Games\Sanctuary\Tiles\Animals;

use Bga\Games\Sanctuary\Constants\Icons;
use Bga\Games\Sanctuary\Constants\Effects;

class A038_Koala_M extends \Bga\Games\Sanctuary\Models\Animal
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'A038_Koala_M';
    $this->name = 'KOALA';
    $this->appeal = '5';
    $this->strength = 3;
    $this->gender = 'M';
    //effect = 'immediate discard up to 2 tiles, gain 1 pouch marker for each';
    $this->effect = [Effects::POUCH => 2];
    $this->categories = [Icons::FOREST, Icons::HERBIVORE];
    $this->continents = [Icons::AUSTRALIA];
    $this->pair = 'A039_Koala_F';
  }
}
