<?php

namespace Bga\Games\Sanctuary\Tiles\Animals;

use Bga\Games\Sanctuary\Constants\Icons;
use BGa\Games\Sanctuary\Constants\Effects;

class A048_EuropeanPondTurtle_N extends \Bga\Games\Sanctuary\Models\Animal
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'A048_EuropeanPondTurtle_N';
    $this->name = 'EUROPEAN POND TURTLE';
    $this->appeal = '2';
    $this->strength = 3;
    $this->gender = 'N';
    //effect = 'immediate take 1 conservation token';
    $this->categories = [Icons::WATER, Icons::REPTILE];
    $this->effect = [Effects::CONSERVATION => 1];

    $this->continents = [Icons::EUROPE];
  }
}
