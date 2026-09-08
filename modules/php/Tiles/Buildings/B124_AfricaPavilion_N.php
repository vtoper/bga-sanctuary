<?php

namespace Bga\Games\Sanctuary\Tiles\Buildings;

use Bga\Games\Sanctuary\Constants\Icons;
use Bga\Games\Sanctuary\Constants\Effects;
use Bga\Games\Sanctuary\Constants\Prerequisites;

class B124_AfricaPavilion_N extends \Bga\Games\Sanctuary\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B124_AfricaPavilion_N';
    $this->name = 'AFRICA PAVILION';
    $this->appeal = '1 per connected ' . Icons::AFRICA;
    $this->gender = 'N';
    //effect = '####ongoingwhen you play an africa tile, draw 1 tile from the pile#####prerequisite by the river';
    $this->prerequisites = [Prerequisites::BY_THE_RIVER => true];
    $this->continents = [Icons::AFRICA];
    $this->listeningIcon = Icons::AFRICA;
    $this->listeningBonuses = [[Effects::DRAW_TILE => 1]];
  }
}
