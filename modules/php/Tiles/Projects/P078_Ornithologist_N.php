<?php

namespace Bga\Games\Sanctuary\Tiles\Projects;

use Bga\Games\Sanctuary\Constants\Icons;
use Bga\Games\Sanctuary\Constants\Effects;
use Bga\Games\Sanctuary\Models\Tile;

class P078_Ornithologist_N extends \Bga\Games\Sanctuary\Models\Project
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'P078_Ornithologist_N';
    $this->name = 'ORNITHOLOGIST';
    $this->appeal = '1 per bird';
    $this->strength = 4;
    $this->gender = 'N';
    //effect = '####ongoingwhen you play a bird, place 1 open area from the pile in your zoo';
    $this->categories = [Icons::BIRD];
    $this->listeningIcon = Icons::BIRD;
    $this->listeningBonuses = [[Effects::PLACE_OPEN_AREAS => 1]];
  }
}
