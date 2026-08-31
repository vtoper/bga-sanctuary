<?php

namespace Bga\Games\Sanctuary\Tiles\Animals;

use Bga\Games\Sanctuary\Constants\Icons;
use Bga\Games\Sanctuary\Constants\Effects;

class A013_Jaguar_F extends \Bga\Games\Sanctuary\Models\Animal
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'A013_Jaguar_F';
    $this->name = 'JAGUAR';
    $this->appeal = '7';
    $this->strength = 4;
    $this->gender = 'F';
    $this->effect = [Effects::HUNTER => 3];
    $this->categories = [Icons::FOREST, Icons::PREDATOR];
    $this->continents = [Icons::AMERICAS];
    $this->openAreas = ['NE'];
    $this->pair = 'A012_Jaguar_M';
  }
}
