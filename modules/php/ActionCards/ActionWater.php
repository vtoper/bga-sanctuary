<?php

namespace Bga\Games\sanctuary\ActionCards;

use \Bga\Games\sanctuary\States\Actions\Project;
use \Bga\Games\sanctuary\States\Actions\TakeTile;

use \Bga\Games\sanctuary\Framework\Engine\Engine;
use \Bga\Games\Sanctuary\Constants\Icons;
use Bga\Games\Sanctuary\States\Actions\Animal;
use \Bga\Games\Sanctuary\States\Actions\PlayTile;
use \Bga\Games\Sanctuary\States\Actions\DrawTile;

class ActionWater extends \Bga\Games\sanctuary\Models\ActionCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->actionType = 'Water';
    $this->descI = [clienttranslate('Maximum animal level'), clienttranslate('Play 1 Animal with the habitat WATER or undefined'),  clienttranslate('Draw 2 tiles from the pile')];
    $this->descII = [clienttranslate('Maximum animal level'), clienttranslate('Play 1 or 2 Animals with the habitat WATER or undefined'),  clienttranslate('Draw 2 tiles from the pile')];
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
              'habitat' => Icons::WATER
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

}
