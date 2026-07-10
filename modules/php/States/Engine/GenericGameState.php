<?php

declare(strict_types=1);

namespace Bga\Games\Sanctuary\States\Engine;

use Bga\GameFramework\StateType;
use Bga\GameFramework\States\GameState;
use Bga\Games\Sanctuary\Framework\Engine\Constants\States;
use Bga\Games\Sanctuary\Game;

class GenericGameState extends GameState
{
    function __construct(
        protected Game $game,
    ) {
        parent::__construct(
            $game,
            id: States::ST_GENERIC_GAME_STATE,
            type: StateType::GAME,
        );
    }

    function onEnteringState(int $activePlayerId) {}
}
