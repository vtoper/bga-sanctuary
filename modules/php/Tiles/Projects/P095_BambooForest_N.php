<?php

namespace Bga\Games\Sanctuary\Tiles\Projects;

use Bga\Games\Sanctuary\Constants\Icons;

class P095_BambooForest_N extends \Bga\Games\Sanctuary\Models\Project
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'P095_BambooForest_N';
    $this->name = 'BAMBOO FOREST';
    $this->appeal = '4';
    $this->strength = 4;
    $this->gender = 'N';
    //effect = 'immediate release 1 herbivore, get 2/3 conservation tokens';
    $this->categories = [Icons::FOREST, Icons::WATER];
    $this->release = true;
    $this->releaseIcon = Icons::HERBIVORE;
  }
}
