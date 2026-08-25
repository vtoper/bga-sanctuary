<?php

namespace Bga\Games\Sanctuary\Tiles\Projects;

use Bga\Games\Sanctuary\Constants\Icons;

class P087_ChitwanNationalPark_N extends \Bga\Games\Sanctuary\Models\Project
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'P087_ChitwanNationalPark_N';
    $this->name = 'CHITWAN NATIONAL PARK';
    $this->appeal = '4';
    $this->strength = 4;
    $this->gender = 'N';
    //effect = 'immediate release 1 asia animal, get 2/3 conservation tokens';
    $this->categories = [Icons::WATER];
    $this->continents = [Icons::ASIA];
    $this->release = true;
    $this->releaseIcon = Icons::ASIA;
  }
}
