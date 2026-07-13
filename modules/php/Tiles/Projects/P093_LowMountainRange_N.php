<?php

namespace Bga\Games\Sanctuary\Tiles\Projects;

use Bga\Games\Sanctuary\Constants\Icons;

class P093_LowMountainRange_N extends \Bga\Games\Sanctuary\Models\Project
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'P093_LowMountainRange_N';
    $this->name = 'LOW MOUNTAIN RANGE';
    $this->appeal = '4';
    $this->strength = 4;
    $this->gender = 'N';
    //effect = 'immediate release 1 bird, get 2/3 conservation tokens';
    $this->categories = [Icons::FOREST, Icons::ROCK];
  }
}
