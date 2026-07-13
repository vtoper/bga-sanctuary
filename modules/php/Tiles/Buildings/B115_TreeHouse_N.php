<?php

namespace Bga\Games\Sanctuary\Tiles\Buildings;

use Bga\Games\Sanctuary\Constants\Icons;

class B115_TreeHouse_N extends \Bga\Games\Sanctuary\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B115_TreeHouse_N';
    $this->name = 'TREE HOUSE';
    $this->appeal = '4';
    $this->gender = 'N';
    //effect = '#####prerequisite next to 2 forest tiles';
    $this->categories = [Icons::FOREST];
  }
}
