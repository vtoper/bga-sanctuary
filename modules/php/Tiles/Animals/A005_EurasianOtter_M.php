<?php

namespace Bga\Games\Sanctuary\Tiles\Animals;

use Bga\Games\Sanctuary\Constants\Icons;

class A005_EurasianOtter_M extends \Bga\Games\Sanctuary\Models\Animal
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'A005_EurasianOtter_M';
    $this->name = 'EURASIAN OTTER';
    $this->appeal = '2 per connected water';
    $this->strength = 3;
    $this->gender = 'M';
    //effect = '####ongoing ';
    $this->categories = [Icons::WATER, Icons::PREDATOR];
    $this->continents = [Icons::EUROPE];
    $this->pair = 'A006_EurasianOtter_F';
  }
}
