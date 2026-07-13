<?php

namespace Bga\Games\Sanctuary\Tiles\Projects;

use Bga\Games\Sanctuary\Constants\Icons;

class P094_Jungle_N extends \Bga\Games\Sanctuary\Models\Project
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'P094_Jungle_N';
    $this->name = 'JUNGLE';
    $this->appeal = '4';
    $this->strength = 4;
    $this->gender = 'N';
    //effect = 'immediate release 1 primate, get 2/3 conservation tokens';
    $this->categories = [Icons::FOREST, Icons::WATER];
  }
}
