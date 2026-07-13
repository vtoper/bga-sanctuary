<?php

namespace Bga\Games\Sanctuary\Tiles\Buildings;

use Bga\Games\Sanctuary\Constants\Icons;

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
    $this->categories = [Icons::FOREST];
  }
}
