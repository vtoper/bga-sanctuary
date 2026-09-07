<?php

namespace Bga\Games\Sanctuary\Tiles\Buildings;

use Bga\Games\Sanctuary\Constants\Icons;
use Bga\Games\Sanctuary\Constants\Prerequisites;
use Bga\Games\Sanctuary\Constants\Effects;
use Bga\Games\Sanctuary\Models\Tile;

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
    $this->prerequisites = [Prerequisites::BY_THE_RIVER => true];
    $this->continents = [Icons::AUSTRALIA];
    $this->listeningIcon = Icons::AUSTRALIA;
    $this->listeningBonuses = [[Effects::POUCH => 1]];
  }
}
