<?php

namespace Bga\Games\sanctuary\ActionCards;

use \Bga\Games\sanctuary\States\Actions\Project;
use \Bga\Games\sanctuary\States\Actions\TakeTile;
use \Bga\Games\sanctuary\Framework\Engine\Engine;

class ActionProject extends \Bga\Games\sanctuary\Models\ActionCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->actionType = 'Project';
    $this->descI = [clienttranslate('Tile Range'), clienttranslate('Project level'), clienttranslate('Play 1 project'), clienttranslate('Take any 1 tile from the display')];
    $this->descII = [clienttranslate('Tile Range'), clienttranslate('Project level'), clienttranslate('Play 1 project'), clienttranslate('Take any 2 tiles from the display')];
    $this->tooltip = [
      clienttranslate('TBD')
    ];
  }

  public function getBaseNode($strength = null)
  {
    $strength = $strength ?? $this->getCurrentStrength();
    $node =
      [
        'type' => Engine::NODE_XOR,
        'children' => [
          [
            "state" => Project::class,
            "args" => [
              'strength' => $strength,
              'lvl' => $this->getLevel(),
            ]
          ],
          [
            "state" => TakeTile::class,
            "args" => [
              'inRange' => false,
              'max' => $this->getLevel()
            ]
          ],
        ]
      ];

    return $node;
  }

  public function getFlow($strength = null)
  {
    $animalNode = $this->getBaseNode($strength);

    return $animalNode;
  }

  // Can always be played
  // public function canBePlayed($player, $strength = null)
  // {
  //   $strength = $strength ?? $this->getStrength();
  //   if ($strength >= 5 && $this->getLevel() == 2) {
  //     return true;
  //   } else {
  //     return parent::canBePlayed($player, $strength);
  //   }
  // }
}
