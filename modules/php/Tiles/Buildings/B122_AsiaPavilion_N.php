<?php

namespace Bga\Games\Sanctuary\Tiles\Buildings;

use Bga\Games\Sanctuary\Constants\Icons;

class B122_AsiaPavilion_N extends \Bga\Games\Sanctuary\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B122_AsiaPavilion_N';
    $this->name = 'ASIA PAVILION';
    $this->appeal = '1 per connected asia';
    $this->gender = 'N';
    //effect = '####ongoingwhen you play a asia tile, take 1 project from the display#####prerequisite by the river';
    $this->continents = [Icons::ASIA];
  }
}
