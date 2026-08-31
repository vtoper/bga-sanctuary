<?php

namespace Bga\Games\Sanctuary\Tiles\Animals;

use Bga\Games\Sanctuary\Constants\Icons;
use Bga\Games\Sanctuary\Constants\Effects;

class A043_GalapagosGiantTortoise_N extends \Bga\Games\Sanctuary\Models\Animal
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'A043_GalapagosGiantTortoise_N';
    $this->name = 'GALAPAGOS GIANT TORTOISE';
    $this->appeal = '7';
    $this->strength = 5;
    $this->gender = 'N';
    //effect = 'immediate take 1 conservation token';
    $this->effect = [Effects::CONSERVATION => 1];

    $this->categories = [Icons::UNDEFINED, Icons::REPTILE];
    $this->continents = [Icons::AMERICAS];
  }
}
