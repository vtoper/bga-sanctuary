<?php

namespace Bga\Games\Sanctuary\Tiles\Animals;

use Bga\Games\Sanctuary\Constants\Icons;
use Bga\Games\Sanctuary\Constants\Effects;

class A007_Caracal_N extends \Bga\Games\Sanctuary\Models\Animal
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'A007_Caracal_N';
    $this->name = 'CARACAL';
    $this->appeal = '3';
    $this->strength = 2;
    $this->gender = 'N';
    $this->effect = [Effects::HUNTER => 3];
    $this->categories = [Icons::ROCK, Icons::PREDATOR];
    $this->continents = [Icons::AFRICA];
    $this->openAreas = ['SW'];
  }
}
