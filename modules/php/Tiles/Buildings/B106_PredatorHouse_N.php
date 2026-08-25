<?php

namespace Bga\Games\Sanctuary\Tiles\Buildings;

use Bga\Games\Sanctuary\Constants\Icons;

class B106_PredatorHouse_N extends \Bga\Games\Sanctuary\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B106_PredatorHouse_N';
    $this->name = 'PREDATOR HOUSE';
    $this->appeal = '2 per adjacent predator';
    $this->gender = 'N';
    //effect = '#####prerequisite next to 1 predator';
    $this->prerequisites = [Icons::PREDATOR => 1];
    $this->categories = [Icons::PREDATOR];
  }
}
