<?php

namespace Bga\Games\Sanctuary\Tiles\Buildings;

use Bga\Games\Sanctuary\Constants\Icons;

class B129_ZooKitchen_N extends \Bga\Games\Sanctuary\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B129_ZooKitchen_N';
    $this->name = 'ZOO KITCHEN';
    $this->appeal = 0;
    $this->gender = 'N';
    //effect = 'immediate take 2 conservation tokens#####prerequisite have 5 different animal icons';

  }
}
