<?php

namespace Bga\Games\Sanctuary\Tiles\Buildings;

use Bga\Games\Sanctuary\Constants\Icons;

class B108_BirdTrees_N extends \Bga\Games\Sanctuary\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B108_BirdTrees_N';
    $this->name = 'BIRD TREES';
    $this->appeal = '2 per adjacent bird';
    $this->gender = 'N';
    //effect = '#####prerequisite next to 1 bird';
    $this->prerequisites = [Icons::BIRD => 1];
    $this->categories = [Icons::BIRD];
  }
}
