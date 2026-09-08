<?php

namespace Bga\Games\Sanctuary\Tiles\Buildings;

use Bga\Games\Sanctuary\Constants\Icons;
use Bga\Games\Sanctuary\Constants\Prerequisites;
use Bga\Games\Sanctuary\Constants\Effects;
use Bga\Games\Sanctuary\Models\Tile;

class B125_EuropePavilion_N extends \Bga\Games\Sanctuary\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B125_EuropePavilion_N';
    $this->name = 'EUROPE PAVILION';
    $this->appeal = '1 per connected ' . Icons::EUROPE;
    $this->gender = 'N';
    //effect = '####ongoingwhen you play a europe tile, take 1 animal from the display#####prerequisite by the river';
    $this->prerequisites = [Prerequisites::BY_THE_RIVER => true];
    $this->continents = [Icons::EUROPE];
    $this->listeningIcon = Icons::EUROPE;
    $this->listeningBonuses = [[Effects::TAKE_TILE => Tile::TILE_ANIMAL]];
  }
}
