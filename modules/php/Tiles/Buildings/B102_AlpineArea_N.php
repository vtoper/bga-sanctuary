<?php

namespace Bga\Games\Sanctuary\Tiles\Buildings;

use Bga\Games\Sanctuary\Constants\Icons;

class B102_AlpineArea_N extends \Bga\Games\Sanctuary\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B102_AlpineArea_N';
    $this->name = 'ALPINE AREA';
    $this->appeal = '2 per connected europe';
    $this->gender = 'N';
    //effect = '#####prerequisite next to 2 europe tiles';
    $this->continents = [Icons::EUROPE];
  }
}
