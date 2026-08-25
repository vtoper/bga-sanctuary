<?php

namespace Bga\Games\Sanctuary\Tiles\Buildings;

use Bga\Games\Sanctuary\Constants\Icons;

class B104_OkavangoArea_N extends \Bga\Games\Sanctuary\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B104_OkavangoArea_N';
    $this->name = 'OKAVANGO AREA';
    $this->appeal = '2 per connected africa';
    $this->gender = 'N';
    //effect = '#####prerequisite next to 2 africa tiles';
    $this->prerequisites = [Icons::AFRICA => 2];
    $this->continents = [Icons::AFRICA];
  }
}
