<?php

namespace Bga\Games\Sanctuary\Tiles\Buildings;

use Bga\Games\Sanctuary\Constants\Icons;
use Bga\Games\Sanctuary\Constants\Prerequisites;

class B134_ExcavationSite_N extends \Bga\Games\Sanctuary\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B134_ExcavationSite_N';
    $this->name = 'EXCAVATION SITE';
    $this->appeal = '1 per tile in hand';
    $this->gender = 'N';
    //effect = '#####prerequisite have 7+ tiles in hand';
    $this->prerequisites = [Prerequisites::HAVE_TILES_IN_HAND => 7];
  }
}
