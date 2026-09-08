<?php

namespace Bga\Games\Sanctuary\Tiles\Projects;

use Bga\Games\Sanctuary\Constants\Icons;
use Bga\Games\Sanctuary\Constants\Effects;

class P074_ExpertInPredators_N extends \Bga\Games\Sanctuary\Models\Project
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'P074_ExpertInPredators_N';
    $this->name = 'EXPERT IN PREDATORS';
    $this->appeal = '1 per ' . Icons::PREDATOR;
    $this->strength = 4;
    $this->gender = 'N';
    //effect = '####ongoingwhen you play a predator, draw 3 tiles from the pile, keep 1 animal';
    $this->categories = [Icons::PREDATOR];
    $this->listeningIcon = Icons::PREDATOR;
    $this->listeningBonuses = [[Effects::HUNTER => 3]];
  }
}
