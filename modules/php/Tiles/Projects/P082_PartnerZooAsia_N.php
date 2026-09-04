<?php

namespace Bga\Games\Sanctuary\Tiles\Projects;

use Bga\Games\Sanctuary\Constants\Icons;
use Bga\Games\Sanctuary\Constants\Effects;

class P082_PartnerZooAsia_N extends \Bga\Games\Sanctuary\Models\Project
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'P082_PartnerZooAsia_N';
    $this->name = 'PARTNER ZOO ASIA';
    $this->appeal = 0;
    $this->strength = 3;
    $this->gender = 'N';
    //effect = 'immediate take 1 asia tile from the display####ongoingplay asia tile with 2 less action strength';
    $this->continents = [Icons::ASIA];
    $this->effect = [Effects::TAKE_TILE => Icons::ASIA];
  }
}
