<?php

namespace Bga\Games\Sanctuary\Tiles\Buildings;

use Bga\Games\Sanctuary\Constants\Icons;

class B135_Statue_N extends \Bga\Games\Sanctuary\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B135_Statue_N';
    $this->name = 'STATUE';
    $this->appeal = '8';
    $this->gender = 'N';
    //effect = '#####prerequisite discard 4 tiles';
  }
}
