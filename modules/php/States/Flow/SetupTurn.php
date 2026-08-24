<?php

declare(strict_types=1);

namespace Bga\Games\Sanctuary\States\Flow;

use Bga\GameFramework\StateType;
use Bga\GameFramework\States\GameState;
use Bga\Games\Sanctuary\Framework\Engine\Engine;
use Bga\Games\Sanctuary\Game;
use Bga\Games\Sanctuary\Constants\States;
use Bga\Games\Sanctuary\States\Actions\ChooseActionCard;
use Bga\Games\Sanctuary\States\Actions\TakeTile;
use Bga\Games\Sanctuary\States\Actions\Animal;
use Bga\Games\Sanctuary\Models\Tile;

class SetupTurn extends GameState
{
    function __construct(
        protected Game $game,
    ) {
        parent::__construct(
            $game,
            id: States::ST_SETUP_TURN,
            type: StateType::GAME,
        );
    }

    function onEnteringState(int $activePlayerId)
    {
        $this->game->giveExtraTime($activePlayerId);
        $newNode = [
            "type" => Engine::NODE_SEQUENTIAL,
            "children" => [
                [
                    "state" => TakeTile::class,
                    "args" => ['inRange' => true]
                ],
                [
                    "state" => ChooseActionCard::class,
                    "args" => []
                ],
                [
                    "state" => Animal::class,
                    "args" => [
                        'type' => Tile::TILE_BUILDING,
                    ],
                    'optional' => true
                ],
                // [
                //     "state" => Conservation::class,
                //     "args" => [
                //     ]
                //      "optional"=>true,
                // ],
                // [
                //     "state" => Upgrade::class,
                //     "args" => [
                //     ]
                //      "optional"=>true,
                // ],
                // // [
                //     "state" => Administration::class,
                //     "args" => [
                //     ]
                // ],
            ]
        ];

        Engine::setup($newNode, ['order' => "turn"]);
        return Engine::proceed();
    }
}
