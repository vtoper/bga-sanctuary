<?php

namespace Bga\Games\Sanctuary\Tiles\Buildings;

use Bga\Games\Sanctuary\Constants\Icons;

class B112_AerialCableway_N extends \Bga\Games\Sanctuary\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B112_AerialCableway_N';
    $this->name = 'AERIAL CABLEWAY';
    $this->appeal = '8';
    $this->gender = 'N';
    //effect = '#####prerequisite next to 4 connected rock tiles';
    $this->categories = [Icons::ROCK];
  }
}
