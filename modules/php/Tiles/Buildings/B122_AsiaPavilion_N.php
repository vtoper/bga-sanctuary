<?php

namespace Bga\Games\Sanctuary\Tiles\Buildings;

use Bga\Games\Sanctuary\Constants\Icons;
use Bga\Games\Sanctuary\Constants\Effects;
use Bga\Games\Sanctuary\Constants\Prerequisites;
use Bga\Games\Sanctuary\Models\Tile;

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
    $this->prerequisites = [Prerequisites::BY_THE_RIVER => true];
    $this->continents = [Icons::ASIA];
    $this->listeningIcon = Icons::ASIA;
    $this->listeningBonuses = [[Effects::TAKE_TILE => Tile::TILE_PROJECT]];
  }
}
