<?php

declare(strict_types=1);

namespace Bga\Games\Sanctuary\States\Engine;

use Bga\GameFramework\StateType;
use Bga\GameFramework\States\PossibleAction;
use Bga\GameFramework\UserException;
use Bga\Games\Sanctuary\Framework\Engine\AbstractNode;
use Bga\Games\Sanctuary\Framework\Engine\ActionStateWithRevert;
use Bga\Games\Sanctuary\Framework\Engine\Constants\States;
use Bga\Games\Sanctuary\Framework\Engine\Engine;
use Bga\Games\Sanctuary\Framework\Engine\XorNode;
use Bga\Games\Sanctuary\Game;
use Bga\Games\Sanctuary\Managers\Players;

class ResolveChoice extends ActionStateWithRevert
{
    function __construct(
        protected Game $game,
        protected ?AbstractNode $node = null
    ) {
        parent::__construct(
            $game,
            node: $node,
            id: States::ST_RESOLVE_CHOICE,
            type: StateType::ACTIVE_PLAYER,
            description: clienttranslate('${actplayer} must choose which effect to resolve'),
            descriptionMyTurn: clienttranslate('${you} must choose which effect to resolve'),
        );
    }

    public function getCustomStateDescription()
    {
        if ($this->getNode() instanceof XorNode) {
            return [
                'description' => clienttranslate('${actplayer} must choose exactly one effect'),
                'descriptionMyTurn' => clienttranslate('${you} must choose exactly one effect'),
            ];
        }

        return null;
    }

    public function getActionArgs(int $activePlayerId): array
    {
        $player = Players::get($activePlayerId);
        $args = array_merge($this->getNodeArgs() ?? [], [
            'choices' => Engine::getNextChoices($player),
            'allChoices' => Engine::getNextChoices($player, true),
        ]);
        $sourceId = $this->getNode()->getSourceId() ?? null;
        if (!isset($args['source']) && !is_null($sourceId)) {
            $args['sourceId'] = $sourceId;
        }
        return $args;
    }

    /**
     * Player must resolve the choice.
     *
     * @throws UserException
     */
    #[PossibleAction]
    public function actChooseAction(int $activePlayerId, int $choiceId)
    {
        $player = Players::get($activePlayerId);
        return Engine::chooseNode($player, $choiceId);
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
        $player = Players::get($playerId);
        $choices = Engine::getNextChoices($player);
        $choiceId = array_rand($choices);
        return Engine::chooseNode($player, $choiceId);
    }
}
