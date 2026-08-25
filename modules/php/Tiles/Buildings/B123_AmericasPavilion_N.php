<?php

namespace Bga\Games\Sanctuary\Tiles\Buildings;

use Bga\Games\Sanctuary\Constants\Icons;
use Bga\Games\Sanctuary\Constants\Prerequisites;

class B123_AmericasPavilion_N extends \Bga\Games\Sanctuary\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B123_AmericasPavilion_N';
    $this->name = 'AMERICAS PAVILION';
    $this->appeal = '1 per connected americas';
    $this->gender = 'N';
    //effect = '####ongoingwhen you play an americas tile, move 1 action to position 1#####prerequisite by the river';
    $this->prerequisites = [Prerequisites::BY_THE_RIVER => true];
    $this->continents = [Icons::AMERICAS];
  }
}
