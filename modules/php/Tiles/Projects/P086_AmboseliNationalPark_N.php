<?php

namespace Bga\Games\Sanctuary\Tiles\Projects;

use Bga\Games\Sanctuary\Constants\Icons;

class P086_AmboseliNationalPark_N extends \Bga\Games\Sanctuary\Models\Project
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'P086_AmboseliNationalPark_N';
    $this->name = 'AMBOSELI NATIONAL PARK';
    $this->appeal = '4';
    $this->strength = 4;
    $this->gender = 'N';
    //effect = 'immediate release 1 africa animal, get 2/3 conservation tokens';
    $this->categories = [Icons::ROCK];
    $this->continents = [Icons::AFRICA];
    $this->release = true;
    $this->releaseIcon = Icons::AFRICA;
  }
}
