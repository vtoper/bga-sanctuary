<?php

namespace Bga\Games\Sanctuary\Tiles\Animals;

use Bga\Games\Sanctuary\Constants\Icons;
use Bga\Games\Sanctuary\Constants\Effects;

class A055_GuianaSpiderMonkey_M extends \Bga\Games\Sanctuary\Models\Animal
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'A055_GuianaSpiderMonkey_M';
    $this->name = 'GUIANA SPIDER MONKEY';
    $this->appeal = '2 per tile in hand';
    $this->strength = 5;
    $this->gender = 'M';
    //effect = 'immediate take 1 conservation token';
    $this->effect = [Effects::CONSERVATION => 1];

    $this->categories = [Icons::FOREST, Icons::PRIMATE];
    $this->continents = [Icons::AMERICAS];
    $this->openAreas = ['N', 'SW'];
    $this->pair = 'A056_GuianaSpiderMonkey_F';
  }
}
