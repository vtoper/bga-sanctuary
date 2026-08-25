<?php

namespace Bga\Games\Sanctuary\Tiles\Buildings;

use Bga\Games\Sanctuary\Constants\Icons;
use Bga\Games\Sanctuary\Constants\Prerequisites;

class B120_Administration_N extends \Bga\Games\Sanctuary\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B120_Administration_N';
    $this->name = 'ADMINISTRATION';
    $this->appeal = '2 per building';
    $this->gender = 'N';
    //effect = '#####prerequisite next to 2 building tiles';
    $this->prerequisites = [Prerequisites::NEXT_TO_BUILDINGS => 2];
  }
}
