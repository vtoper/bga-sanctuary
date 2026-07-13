<?php

namespace Bga\Games\Sanctuary\Tiles\Buildings;

use Bga\Games\Sanctuary\Constants\Icons;

class B121_HiddenGlassWall_N extends \Bga\Games\Sanctuary\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B121_HiddenGlassWall_N';
    $this->name = 'HIDDEN GLASS WALL';
    $this->appeal = '6';
    $this->gender = 'N';
    //effect = '#####prerequisite next to 2 large animals';

  }
}
