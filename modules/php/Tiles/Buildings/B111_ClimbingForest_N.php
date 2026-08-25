<?php

namespace Bga\Games\Sanctuary\Tiles\Buildings;

use Bga\Games\Sanctuary\Constants\Icons;
use Bga\Games\Sanctuary\Constants\Prerequisites;

class B111_ClimbingForest_N extends \Bga\Games\Sanctuary\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B111_ClimbingForest_N';
    $this->name = 'CLIMBING FOREST';
    $this->appeal = '8';
    $this->gender = 'N';
    //effect = '#####prerequisite next to 4 connected forest tiles';
    $this->prerequisites = [Prerequisites::CONNECT_PREFIX . Icons::FOREST => 4];
    $this->categories = [Icons::FOREST];
  }
}
