<?php

declare(strict_types=1);

namespace Bga\Games\Sanctuary\States\Flow;

use Bga\GameFramework\StateType;
use Bga\GameFramework\States\GameState;
use Bga\Games\Sanctuary\Framework\Engine\Engine;
use Bga\Games\Sanctuary\Game;
use Bga\Games\Sanctuary\Constants\States;
use Bga\Games\Sanctuary\States\Actions\ChooseActionCard;

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
            "state" => ChooseActionCard::class,
            "args" => []
        ];

        Engine::setup($newNode, ['order' => "turn"]);
        return Engine::proceed();
    }
}
