<?php

namespace Bga\Games\Sanctuary\Maps;

use ARK\Helpers\Utils;


class Map1 extends \Bga\Games\Sanctuary\Models\ZooMap
{
  protected $id = '1';
  public function __construct($player)
  {
    // $this->name = clienttranslate('Observation Tower');
    // $this->desc = clienttranslate(
    //   'Gain <APPEAL:2> every time you flip a standard enclosure to its occupied side next to the <TOWER>.'
    // );
    parent::__construct($player);
  }

  protected $bonuses = [
    // '0_1' => [XTOKEN => 1],
    // '0_11' => [MONEY => 5],
    // '3_6' => [TAKE_IN_RANGE_OR_DECK => 1],
    // '4_1' => [CLEVER => 1],
    // '4_11' => [REPUTATION => 1],
    // '6_5' => [MONEY => 5],
    // '7_2' => [TAKE_IN_RANGE_OR_DECK => 1],
    // '7_8' => [XTOKEN => 1],
    // '7_12' => [XTOKEN => 1],
  ];
}
