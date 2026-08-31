<?php

namespace Bga\Games\Sanctuary\Tiles\Animals;

use Bga\Games\Sanctuary\Constants\Icons;
use bga\Games\Sanctuary\Constants\Effects;

class A056_GuianaSpiderMonkey_F extends \Bga\Games\Sanctuary\Models\Animal
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'A056_GuianaSpiderMonkey_F';
    $this->name = 'GUIANA SPIDER MONKEY';
    $this->appeal = '2 per tile in hand';
    $this->strength = 5;
    $this->gender = 'F';
    //effect = 'immediate take 1 conservation token';
    $this->effect = [Effects::CONSERVATION => 1];

    $this->categories = [Icons::FOREST, Icons::PRIMATE];
    $this->continents = [Icons::AMERICAS];
    $this->openAreas = ['N', 'NE'];
    $this->pair = 'A055_GuianaSpiderMonkey_M';
  }
}
