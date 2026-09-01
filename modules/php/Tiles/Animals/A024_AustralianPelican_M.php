<?php

namespace Bga\Games\Sanctuary\Tiles\Animals;

use Bga\Games\Sanctuary\Constants\Icons;
use Bga\Games\Sanctuary\Constants\Effects;

class A024_AustralianPelican_M extends \Bga\Games\Sanctuary\Models\Animal
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'A024_AustralianPelican_M';
    $this->name = 'AUSTRALIAN PELICAN';
    $this->appeal = '6';
    $this->strength = 4;
    $this->gender = 'M';
    //effect = 'immediate place 1 open area from the pile in your zoo';
    $this->effect = [Effects::PLACE_OPEN_AREAS => 1];
    $this->categories = [Icons::WATER, Icons::BIRD];
    $this->continents = [Icons::AUSTRALIA];
    $this->pair = 'A025_AustralianPelican_F';
  }
}
