<?php

namespace Bga\Games\Sanctuary\Tiles\Buildings;

use Bga\Games\Sanctuary\Constants\Icons;
use Bga\Games\Sanctuary\Constants\Prerequisites;

class B133_Fountain_N extends \Bga\Games\Sanctuary\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B133_Fountain_N';
    $this->name = 'FOUNTAIN';
    $this->appeal = '6';
    $this->gender = 'N';
    //effect = '#####prerequisite have tiles on all spaces by the river';
    $this->prerequisites = [Prerequisites::HAVE_ALL_RIVER_SPACES_FILLED => true];
  }
}
