<?php

namespace Bga\Games\Sanctuary\Tiles\Animals;

use Bga\Games\Sanctuary\Constants\Icons;
use Bga\Games\Sanctuary\Constants\Effects;

class A052_InlandTaipan_N extends \Bga\Games\Sanctuary\Models\Animal
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'A052_InlandTaipan_N';
    $this->name = 'INLAND TAIPAN';
    $this->appeal = '6';
    $this->strength = 4;
    $this->gender = 'N';
    //effect = 'immediate relocate 1 tile in your zoo';
    $this->effect = [Effects::RELOCATE => 1];
    $this->categories = [Icons::ROCK, Icons::REPTILE];
    $this->continents = [Icons::AUSTRALIA];
  }
}
