<?php

namespace Bga\Games\Sanctuary\Tiles\Buildings;

use Bga\Games\Sanctuary\Constants\Icons;
use Bga\Games\Sanctuary\Constants\Prerequisites;

class B117_ObservationDeck_N extends \Bga\Games\Sanctuary\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B117_ObservationDeck_N';
    $this->name = 'OBSERVATION DECK';
    $this->appeal = '1 per different adjacent icon';
    $this->gender = 'N';
    //effect = '#####prerequisite next to 4 different animal or continent icons';
    $this->prerequisites = [Prerequisites::NEXT_TO_DIFFERENT_ANIMAL_OR_CONTINENT_ICONS => 4];
    $this->categories = [Icons::ROCK];
  }
}
