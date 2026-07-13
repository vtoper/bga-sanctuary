<?php

namespace Bga\Games\Sanctuary\Tiles\Buildings;

use Bga\Games\Sanctuary\Constants\Icons;

class B128_ConferenceHall_N extends \Bga\Games\Sanctuary\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B128_ConferenceHall_N';
    $this->name = 'CONFERENCE HALL';
    $this->appeal = '1 per project';
    $this->gender = 'N';
    //effect = 'immediate take 2 conservation tokens#####prerequisite have 5 different continent icons';

  }
}
