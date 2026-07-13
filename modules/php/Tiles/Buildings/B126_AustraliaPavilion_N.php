<?php

namespace Bga\Games\Sanctuary\Tiles\Buildings;

use Bga\Games\Sanctuary\Constants\Icons;

class B126_AustraliaPavilion_N extends \Bga\Games\Sanctuary\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B126_AustraliaPavilion_N';
    $this->name = 'AUSTRALIA PAVILION';
    $this->appeal = '1 per connected australia';
    $this->gender = 'N';
    //effect = '####ongoingwhen you play an australia tile, discard 1 tile to gain 1 pouch token#####prerequisite by the river';
    $this->continents = [Icons::AUSTRALIA];
  }
}
