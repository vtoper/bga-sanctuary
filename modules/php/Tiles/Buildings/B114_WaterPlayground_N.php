<?php

namespace Bga\Games\Sanctuary\Tiles\Buildings;

use Bga\Games\Sanctuary\Constants\Icons;

class B114_WaterPlayground_N extends \Bga\Games\Sanctuary\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B114_WaterPlayground_N';
    $this->name = 'WATER PLAYGROUND';
    $this->appeal = '4';
    $this->gender = 'N';
    //effect = '#####prerequisite next to 2 water tiles';
    $this->categories = [Icons::WATER];
  }
}
