<?php

namespace Bga\Games\sanctuary\ActionCards;

use \Bga\Games\sanctuary\States\Actions\Project;
use \Bga\Games\sanctuary\States\Actions\TakeTile;

use \Bga\Games\sanctuary\Framework\Engine\Engine;
use \Bga\Games\Sanctuary\Constants\Icons;
use \Bga\Games\Sanctuary\States\Actions\Animal;
use \Bga\Games\Sanctuary\States\Actions\DrawTile;

class ActionRock extends \Bga\Games\sanctuary\Models\ActionCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->actionType = 'Rock';
    $this->descI = [clienttranslate('Maximum animal level'), clienttranslate('Play 1 Animal with the habitat ROCK or undefined'),  clienttranslate('Draw 2 tiles from the pile')];
    $this->descII = [clienttranslate('Maximum animal level'), clienttranslate('Play 1 or 2 Animals with the habitat ROCK or undefined'),  clienttranslate('Draw 2 tiles from the pile')];
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
            "state" => Animal::class,
            "args" => [
              'strength' => $strength,
              'nb' => $this->getLevel(),
              'habitat' => Icons::ROCK
            ]
          ],
          [
            "state" => DrawTile::class,
            "args" => [
              'n' => 2
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
