<?php

namespace Bga\Games\Sanctuary\Tiles\Projects;

use Bga\Games\Sanctuary\Constants\Icons;

class P091_Canyon_N extends \Bga\Games\Sanctuary\Models\Project
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'P091_Canyon_N';
    $this->name = 'CANYON';
    $this->appeal = '4';
    $this->strength = 4;
    $this->gender = 'N';
    //effect = 'immediate release 1 reptile, get 2/3 conservation tokens';
    $this->categories = [Icons::WATER, Icons::ROCK];
  }
}
