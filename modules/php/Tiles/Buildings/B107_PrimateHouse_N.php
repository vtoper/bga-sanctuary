<?php

namespace Bga\Games\Sanctuary\Tiles\Buildings;

use Bga\Games\Sanctuary\Constants\Icons;

class B107_PrimateHouse_N extends \Bga\Games\Sanctuary\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B107_PrimateHouse_N';
    $this->name = 'PRIMATE HOUSE';
    $this->appeal = '2 per adjacent primate';
    $this->gender = 'N';
    //effect = '#####prerequisite next to 1 primate';
    $this->prerequisites = [Icons::PRIMATE => 1];
    $this->categories = [Icons::PRIMATE];
  }
}
