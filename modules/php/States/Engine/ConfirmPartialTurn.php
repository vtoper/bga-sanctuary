<?php

declare(strict_types=1);

namespace Bga\Games\Sanctuary\States\Engine;

use Bga\GameFramework\StateType;
use Bga\GameFramework\States\PossibleAction;
use Bga\GameFramework\UserException;
use Bga\Games\Sanctuary\Framework\Db\Globals;
use Bga\Games\Sanctuary\Framework\Engine\AbstractNode;
use Bga\Games\Sanctuary\Framework\Engine\ActionStateWithRevert;
use Bga\Games\Sanctuary\Framework\Engine\Constants\States;
use Bga\Games\Sanctuary\Framework\Engine\Engine;
use Bga\Games\Sanctuary\Game;
use Bga\Games\Sanctuary\Managers\Players;

class ConfirmPartialTurn extends ActionStateWithRevert
{
    function __construct(
        protected Game $game,
        protected ?AbstractNode $node = null
    ) {
        parent::__construct(
            $game,
            node: $node,
            id: States::ST_CONFIRM_PARTIAL_TURN,
            type: StateType::ACTIVE_PLAYER,
            description: clienttranslate('${actplayer} must confirm switch to ${player_name}'),
            descriptionMyTurn: clienttranslate('${you} must confirm switch to ${player_name}'),
        );
    }

    public function getCustomStateDescription()
    {
        if ($this->getNode()->getPlayerId() == Engine::PLAYER_ID_AUTO) {
            return [
                "description" => clienttranslate('${actplayer} must finalize their decisions.'),
                "descriptionMyTurn" => clienttranslate('${you} must finalize your decisions. When you do you won\'t be able to undo past this point'),
            ];
        }
        return null;
    }

    public function isOptional()
    {
        return false;
    }

    public function getActionArgs(int $activePlayerId): array
    {
        $node = $this->getNode();
        return [
            "player_name" => $node->getPlayerId() == Engine::PLAYER_ID_AUTO ? "" : Players::get($node->getPlayerId())->getName(),
            "player_id" => $node->getPlayerId(),
        ];
    }

    public function onEnteringState(int $activePlayerId)
    {
        // Check user preference to bypass if DISABLED is picked
        if ($this->userPreferences->get($activePlayerId, Globals::PREFERENCE_CONFIRM) == Globals::CONFIRM_DISABLED) {
            return $this->actConfirmTurn();
        }
    }

    /**
     * Player must confirm the turn.
     *
     * @throws UserException
     */
    #[PossibleAction]
    public function actConfirmTurn()
    {
        return Engine::confirmPartialTurn();
    }

    /**
     * This method is called each time it is the turn of a player who has quit the game (= "zombie" player).
     * You can do whatever you want in order to make sure the turn of this player ends appropriately
     * (ex: play a random card).
     * 
     * See more about Zombie Mode: https://en.doc.boardgamearena.com/Zombie_Mode
     *
     * Important: your zombie code will be called when the player leaves the game. This action is triggered
     * from the main site and propagated to the gameserver from a server, not from a browser.
     * As a consequence, there is no current player associated to this action. In your zombieTurn function,
     * you must _never_ use `getCurrentPlayerId()` or `getCurrentPlayerName()`, 
     * but use the $playerId passed in parameter and $this->game->getPlayerNameById($playerId) instead.
     */
    function zombie(int $playerId)
    {
        return Engine::confirmPartialTurn();
    }
}
