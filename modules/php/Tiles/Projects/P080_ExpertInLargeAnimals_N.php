<?php

namespace Bga\Games\Sanctuary\Tiles\Projects;

use Bga\Games\Sanctuary\Constants\Icons;
use Bga\Games\Sanctuary\Constants\Effects;

class P080_ExpertInLargeAnimals_N extends \Bga\Games\Sanctuary\Models\Project
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'P080_ExpertInLargeAnimals_N';
    $this->name = 'EXPERT IN LARGE ANIMALS';
    $this->appeal = 0;
    $this->strength = 4;
    $this->gender = 'N';
    $this->effect = [Effects::TAKE_TILE => Icons::LARGE_ANIMALS];

    //effect = 'immediate take 1 large animal from the display####ongoingplay large animal tile with 1 less action strength';

  }
}
