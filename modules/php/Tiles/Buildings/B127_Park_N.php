<?php

namespace Bga\Games\Sanctuary\Tiles\Buildings;

use Bga\Games\Sanctuary\Constants\Icons;

class B127_Park_N extends \Bga\Games\Sanctuary\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B127_Park_N';
    $this->name = 'PARK';
    $this->appeal = '5';
    $this->gender = 'N';
    //effect = '#####prerequisite next to 2 open areas';

  }
}
