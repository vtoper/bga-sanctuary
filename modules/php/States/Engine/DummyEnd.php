<?php

declare(strict_types=1);

namespace Bga\Games\Sanctuary\States\Engine;

use Bga\GameFramework\States\PossibleAction;
use Bga\GameFramework\StateType;
use Bga\Games\Sanctuary\Framework\Engine\Constants\States;
use Bga\Games\Sanctuary\Game;

class DummyEnd extends \Bga\GameFramework\States\GameState
{

    function __construct(
        protected Game $game,
    ) {
        parent::__construct(
            $game,
            id: States::ST_DUMMY_END,
            type: StateType::MULTIPLE_ACTIVE_PLAYER,
            description: clienttranslate('Please report any possible end of the game bugs and click finish to end the game'),
            descriptionMyTurn: clienttranslate('Please report any possible end of the game bugs and click finish to end the game'),
        );
    }

    public function onEnteringState()
    {
        Game::get()->gamestate->setAllPlayersMultiactive();
    }

    #[PossibleAction]
    public function actEnd()
    {
        return States::ST_END_GAME;
    }

    public function zombie(int $playerId)
    {
        $this->game->gamestate->setPlayerNonMultiactive($playerId, States::ST_END_GAME);
    }
}
