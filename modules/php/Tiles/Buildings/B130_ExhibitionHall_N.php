<?php

namespace Bga\Games\Sanctuary\Tiles\Buildings;

use Bga\Games\Sanctuary\Constants\Icons;
use Bga\Games\Sanctuary\Constants\Prerequisites;

class B130_ExhibitionHall_N extends \Bga\Games\Sanctuary\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B130_ExhibitionHall_N';
    $this->name = 'EXHIBITION HALL';
    $this->appeal = '10';
    $this->gender = 'N';
    //effect = '#####prerequisite have 10 different animal and continent icons';
    $this->prerequisites = [Prerequisites::HAVE_DIFFERENT_ANIMAL_AND_CONTINENT_ICONS => 10];
  }
}
