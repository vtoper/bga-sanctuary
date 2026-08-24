<?php

namespace Bga\Games\Sanctuary\Maps;

use Bga\Games\Sanctuary\Constants\Effects;

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

  protected $startingOpenAreas = [
    ['x' => 1, 'y' => 6],
    ['x' => 5, 'y' => 0],
  ];

  protected $bonuses = [
    '0_1' => [Effects::MOVE_ACTION_CARD => 1],
    '0_5' => [Effects::DRAW_TILE => 1],
    '3_0' => [Effects::TAKE_TILE => 1],
    '6_1' => [Effects::DRAW_TILE => 1],
    '6_5' => [Effects::MOVE_ACTION_CARD => 1],
  ];
}
