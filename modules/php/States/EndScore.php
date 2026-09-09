<?php

declare(strict_types=1);

namespace Bga\Games\Sanctuary\States;

use Bga\GameFramework\StateType;
use Bga\Games\Sanctuary\Game;
use Bga\Games\Sanctuary\Managers\Players;
use Bga\Games\Sanctuary\Constants\States;


class EndScore extends \Bga\GameFramework\States\GameState
{

    function __construct(
        protected Game $game,
    ) {
        parent::__construct(
            $game,
            id: States::ST_END_GAME_SCORING,
            type: StateType::GAME,
        );
    }

    /**
     * Game state action, example content.
     *
     * The onEnteringState method of state `EndScore` is called just before the end of the game.
     */
    public function onEnteringState()
    {
        // Here, we would compute scores if they are not updated live, and compute average statistics
        foreach (Players::getAll() as $pId => $player) {
            $player->computeScore(true);
        }
        return States::ST_END_GAME;
    }
}
