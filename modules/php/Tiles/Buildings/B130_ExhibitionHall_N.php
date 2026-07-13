<?php

namespace Bga\Games\Sanctuary\Tiles\Buildings;

use Bga\Games\Sanctuary\Constants\Icons;

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

  }
}
