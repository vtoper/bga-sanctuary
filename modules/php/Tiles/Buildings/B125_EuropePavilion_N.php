<?php

namespace Bga\Games\Sanctuary\Tiles\Buildings;

use Bga\Games\Sanctuary\Constants\Icons;

class B125_EuropePavilion_N extends \Bga\Games\Sanctuary\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B125_EuropePavilion_N';
    $this->name = 'EUROPE PAVILION';
    $this->appeal = '1 per connected europe';
    $this->gender = 'N';
    //effect = '####ongoingwhen you play a europe tile, take 1 animal from the display#####prerequisite by the river';
    $this->continents = [Icons::EUROPE];
  }
}
