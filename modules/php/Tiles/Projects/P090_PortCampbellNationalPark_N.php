<?php

namespace Bga\Games\Sanctuary\Tiles\Projects;

use Bga\Games\Sanctuary\Constants\Icons;

class P090_PortCampbellNationalPark_N extends \Bga\Games\Sanctuary\Models\Project
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'P090_PortCampbellNationalPark_N';
    $this->name = 'PORT CAMPBELL NATIONAL PARK';
    $this->appeal = '4';
    $this->strength = 4;
    $this->gender = 'N';
    //effect = 'immediate release 1 australia animal, get 2/3 conservation tokens';
    $this->categories = [Icons::ROCK];
    $this->continents = [Icons::AUSTRALIA];
    $this->release = true;
    $this->releaseIcon = Icons::AUSTRALIA;
  }
}
