<?php

namespace Bga\Games\Sanctuary\Tiles\Buildings;

use Bga\Games\Sanctuary\Constants\Icons;

class B101_OutbackArea_N extends \Bga\Games\Sanctuary\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B101_OutbackArea_N';
    $this->name = 'OUTBACK AREA';
    $this->appeal = '2 per connected australia';
    $this->gender = 'N';
    //effect = '#####prerequisite next to 2 australia tiles';
    $this->prerequisites = [Icons::AUSTRALIA => 2];
    $this->continents = [Icons::AUSTRALIA];
  }
}
