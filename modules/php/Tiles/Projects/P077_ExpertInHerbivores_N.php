<?php

namespace Bga\Games\Sanctuary\Tiles\Projects;

use Bga\Games\Sanctuary\Constants\Icons;
use Bga\Games\Sanctuary\Constants\Effects;
use Bga\Games\Sanctuary\Models\Tile;

class P077_ExpertInHerbivores_N extends \Bga\Games\Sanctuary\Models\Project
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'P077_ExpertInHerbivores_N';
    $this->name = 'EXPERT IN HERBIVORES';
    $this->appeal = '1 per ' . Icons::HERBIVORE;
    $this->strength = 4;
    $this->gender = 'N';
    //effect = '####ongoingwhen you play a herbivore, take 1 building from the display';
    $this->categories = [Icons::HERBIVORE];
    $this->listeningIcon = Icons::HERBIVORE;
    $this->listeningBonuses = [[Effects::TAKE_TILE => Tile::TILE_BUILDING]];
  }
}
