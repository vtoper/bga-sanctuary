<?php

namespace Bga\Games\Sanctuary\Tiles\Buildings;

use Bga\Games\Sanctuary\Constants\Icons;

class B110_HerbivoreHouse_N extends \Bga\Games\Sanctuary\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B110_HerbivoreHouse_N';
    $this->name = 'HERBIVORE HOUSE';
    $this->appeal = '2 per adjacent herbivore';
    $this->gender = 'N';
    //effect = '#####prerequisite next to 1 herbivore';
    $this->categories = [Icons::HERBIVORE];
  }
}
