<?php

namespace Bga\Games\Sanctuary\Tiles\Buildings;

use Bga\Games\Sanctuary\Constants\Icons;
use Bga\Games\Sanctuary\Constants\Prerequisites;

class B113_Aquarium_N extends \Bga\Games\Sanctuary\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B113_Aquarium_N';
    $this->name = 'AQUARIUM';
    $this->appeal = '8';
    $this->gender = 'N';
    //effect = '#####prerequisite next to 4 connected water tiles';
    $this->prerequisites = [Prerequisites::CONNECT_PREFIX . Icons::WATER => 4];
    $this->categories = [Icons::WATER];
  }
}
