<?php

namespace Bga\Games\Sanctuary\Tiles\Buildings;

use Bga\Games\Sanctuary\Constants\Icons;

class B109_ReptileHouse_N extends \Bga\Games\Sanctuary\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B109_ReptileHouse_N';
    $this->name = 'REPTILE HOUSE';
    $this->appeal = '2 per adjacent reptile';
    $this->gender = 'N';
    //effect = '#####prerequisite next to 1 reptile';
    $this->categories = [Icons::REPTILE];
  }
}
