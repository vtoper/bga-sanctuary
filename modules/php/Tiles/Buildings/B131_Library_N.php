<?php

namespace Bga\Games\Sanctuary\Tiles\Buildings;

use Bga\Games\Sanctuary\Constants\Icons;
use Bga\Games\Sanctuary\Constants\Prerequisites;

class B131_Library_N extends \Bga\Games\Sanctuary\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B131_Library_N';
    $this->name = 'LIBRARY';
    $this->appeal = '2 per project';
    $this->gender = 'N';
    //effect = '#####prerequisite next to 2 projects';
    $this->prerequisites = [Prerequisites::NEXT_TO_PROJECTS => 2];
  }
}
