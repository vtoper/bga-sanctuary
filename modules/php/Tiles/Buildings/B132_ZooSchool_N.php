<?php

namespace Bga\Games\Sanctuary\Tiles\Buildings;

use Bga\Games\Sanctuary\Constants\Icons;
use Bga\Games\Sanctuary\Constants\Prerequisites;

class B132_ZooSchool_N extends \Bga\Games\Sanctuary\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B132_ZooSchool_N';
    $this->name = 'ZOO SCHOOL';
    $this->appeal = '4';
    $this->gender = 'N';
    //effect = 'immediate take 1 project from the display, take 1 conservation token#####prerequisite by the river';
    $this->prerequisites = [Prerequisites::BY_THE_RIVER => true];
  }
}
