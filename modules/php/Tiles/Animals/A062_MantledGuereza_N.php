<?php

namespace Bga\Games\Sanctuary\Tiles\Animals;

use Bga\Games\Sanctuary\Constants\Icons;
use Bga\Games\Sanctuary\Constants\Effects;

class A062_MantledGuereza_N extends \Bga\Games\Sanctuary\Models\Animal
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'A062_MantledGuereza_N';
    $this->name = 'MANTLED GUEREZA';
    $this->appeal = '6';
    $this->strength = 3;
    $this->gender = 'N';
    //effect = 'immediate move 1 action to position 1';
    $this->effect = [Effects::MOVE_ACTION_CARD => 1];
    $this->categories = [Icons::FOREST, Icons::PRIMATE];
    $this->continents = [Icons::AFRICA];
    $this->openAreas = ['NW'];
  }
}
