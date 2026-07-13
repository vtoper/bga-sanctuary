<?php

namespace Bga\Games\Sanctuary\Tiles\Buildings;

use Bga\Games\Sanctuary\Constants\Icons;

class B116_AdventurePlayground_N extends \Bga\Games\Sanctuary\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B116_AdventurePlayground_N';
    $this->name = 'ADVENTURE PLAYGROUND';
    $this->appeal = '4';
    $this->gender = 'N';
    //effect = '#####prerequisite next to 2 rock tiles';
    $this->categories = [Icons::ROCK];
  }
}
