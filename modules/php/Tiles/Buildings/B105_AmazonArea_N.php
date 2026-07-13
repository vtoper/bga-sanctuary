<?php

namespace Bga\Games\Sanctuary\Tiles\Buildings;

use Bga\Games\Sanctuary\Constants\Icons;

class B105_AmazonArea_N extends \Bga\Games\Sanctuary\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B105_AmazonArea_N';
    $this->name = 'AMAZON AREA';
    $this->appeal = '2 per connected americas';
    $this->gender = 'N';
    //effect = '#####prerequisite next to 2 americas tiles';
    $this->continents = [Icons::AMERICAS];
  }
}
