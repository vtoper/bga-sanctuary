<?php

namespace Bga\Games\Sanctuary\Tiles\Projects;

use Bga\Games\Sanctuary\Constants\Icons;
use Bga\Games\Sanctuary\Constants\Effects;

class P084_PartnerZooAmericas_N extends \Bga\Games\Sanctuary\Models\Project
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'P084_PartnerZooAmericas_N';
    $this->name = 'PARTNER ZOO AMERICAS';
    $this->appeal = 0;
    $this->strength = 3;
    $this->gender = 'N';
    //effect = 'immediate take 1 americas tile from the display####ongoingplay americas tile with 2 less action strength';
    $this->continents = [Icons::AMERICAS];
    $this->effect = [Effects::TAKE_TILE => Icons::AMERICAS];
  }
}
