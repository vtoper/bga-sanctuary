<?php

declare(strict_types=1);

namespace Bga\Games\Sanctuary\States\Actions;

use Bga\GameFramework\StateType;
use Bga\GameFramework\States\GameState;
use Bga\GameFramework\States\PossibleAction;
use Bga\GameFramework\UserException;
use Bga\Games\Sanctuary\Game;
use Bga\Games\Sanctuary\Constants\States;
use Bga\Games\Sanctuary\Framework\Db\Log;
use Bga\Games\Sanctuary\Framework\Engine\AbstractNode;
use Bga\Games\sanctuary\Framework\Engine\AutomaticActionState;
use Bga\Games\Sanctuary\Managers\Players;
use Bga\Games\Sanctuary\Managers\ActionCards;
use Bga\Games\Sanctuary\Managers\Globals;

class Cleanup extends AutomaticActionState
{
    function __construct(
        protected Game $game,
        protected ?AbstractNode $node = null
    ) {
        parent::__construct(
            $game,
            node: $node,
            id: States::ST_CLEANUP,
            type: StateType::GAME,
        );
    }

    function onEnteringState(int $activePlayerId)
    {
        // Sanity check
        $player = Players::getActive();
        $aCard = ActionCards::get($this->getNodeArgs('card', null));
        if (is_null($aCard)) {
            throw new \BgaVisibleSystemException('No card enabled. Should not happen');
        }
        $type = $aCard->getActionType();
        if ($aCard->getStatus() != 1) {
            throw new \BgaVisibleSystemException('Card not enabled. Should not happen');
        }

        // Slide action card to position 1 and notify
        $aCard->setStatus(0);
        $actionCards = $player->moveActionCard($type, 1);
        $this->notify->all(
            'actionCardCleanup',
            $msg ?? clienttranslate('${player_name} places action card ${action_card_name}${action_card_icon} at position ${position} (finishing action)'),
            [
                'i18n' => ['card_type'],
                'player' => $player,
                'actionCard' => $aCard,
                'position' => 1,
                'actionCards' => $actionCards,
            ]
        );

        Globals::setActiveActionCard([]);
        $this->resolve(['cleanup' => 'done']);
    }
}
