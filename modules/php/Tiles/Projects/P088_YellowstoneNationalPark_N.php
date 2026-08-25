<?php

namespace Bga\Games\Sanctuary\Tiles\Projects;

use Bga\Games\Sanctuary\Constants\Icons;

class P088_YellowstoneNationalPark_N extends \Bga\Games\Sanctuary\Models\Project
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'P088_YellowstoneNationalPark_N';
    $this->name = 'YELLOWSTONE NATIONAL PARK';
    $this->appeal = '4';
    $this->strength = 4;
    $this->gender = 'N';
    //effect = 'immediate release 1 americas animal, get 2/3 conservation tokens';
    $this->categories = [Icons::FOREST];
    $this->continents = [Icons::AMERICAS];
    $this->release = true;
    $this->releaseIcon = Icons::AMERICAS;
  }
}
