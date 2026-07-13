<?php

namespace Bga\Games\Sanctuary\Tiles\Buildings;

use Bga\Games\Sanctuary\Constants\Icons;

class B103_SumatraArea_N extends \Bga\Games\Sanctuary\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B103_SumatraArea_N';
    $this->name = 'SUMATRA AREA';
    $this->appeal = '2 per connected asia';
    $this->gender = 'N';
    //effect = '#####prerequisite next to 2 asia tiles';
    $this->continents = [Icons::ASIA];
  }
}
