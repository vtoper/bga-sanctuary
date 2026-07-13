<?php

namespace Bga\Games\Sanctuary\Tiles\Buildings;

use Bga\Games\Sanctuary\Constants\Icons;

class B118_ObservationDeck_N extends \Bga\Games\Sanctuary\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B118_ObservationDeck_N';
    $this->name = 'OBSERVATION DECK';
    $this->appeal = '1 per different adjacent icon';
    $this->gender = 'N';
    //effect = '#####prerequisite next to 4 different animal or continent icons';
    $this->categories = [Icons::FOREST];
  }
}
