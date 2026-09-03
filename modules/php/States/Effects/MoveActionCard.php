<?php

declare(strict_types=1);

namespace Bga\Games\Sanctuary\States\Effects;

use Bga\GameFramework\StateType;
use Bga\GameFramework\States\PossibleAction;
use Bga\GameFramework\UserException;
use Bga\Games\Sanctuary\Constants\States;
use Bga\Games\Sanctuary\Framework\Engine\AbstractNode;
use Bga\Games\Sanctuary\Framework\Engine\ActionStateWithRevert;
use Bga\Games\Sanctuary\Game;
use Bga\Games\Sanctuary\Managers\ActionCards;
use Bga\Games\Sanctuary\Managers\Players;

class MoveActionCard extends ActionStateWithRevert
{
    function __construct(
        protected Game $game,
        protected ?AbstractNode $node = null
    ) {
        parent::__construct(
            $game,
            node: $node,
            id: States::ST_MOVE_ACTION_CARD,
            type: StateType::ACTIVE_PLAYER,
            description: clienttranslate('${actplayer} must move an action card to slot 1'),
            descriptionMyTurn: clienttranslate('${you} must move an action card to slot 1'),
        );
    }

    public function getActionArgs(int $activePlayerId): array
    {
        return [
            '_private' => [
                $activePlayerId => [
                    'actionCards' => Players::get($activePlayerId)->getActionCards()->map(fn($card) => [
                        'id' => $card->getId(),
                        'type' => $card->getActionType(),
                        'strength' => $card->getStrength(),
                    ])->toArray(),
                ],
            ],
            '_merge_private' => true,
        ];
    }

    #[PossibleAction]
    public function actMoveActionCard(int $cardId)
    {
        $player = Players::getCurrent();
        if ($player != Players::getActive()) {
            throw new UserException(clienttranslate('Not your turn'));
        }

        $actionCard = ActionCards::get($cardId);
        if ($actionCard === null || $actionCard->getPId() != $player->getId()) {
            throw new UserException(clienttranslate('This action card cannot be moved'));
        }

        $actionCards = $player->moveActionCard($actionCard->getActionType(), 1);
        $this->notify->all('actionCardMoved', clienttranslate('${player_name} moves ${action_card_name} to slot 1'), [
            'player' => $player,
            'actionCard' => $actionCard,
            'actionCards' => $actionCards->toArray(),
        ]);

        return $this->resolve(['cardId' => $cardId]);
    }

    function zombie(int $playerId)
    {
        $actionCard = Players::get($playerId)->getActionCards()->first();
        if ($actionCard === null) {
            return $this->resolve(['cardId' => null]);
        }

        $actionCards = Players::get($playerId)->moveActionCard($actionCard->getActionType(), 1);
        $this->notify->all('actionCardMoved', clienttranslate('${player_name} moves ${action_card_name} to slot 1'), [
            'player' => Players::get($playerId),
            'actionCard' => $actionCard,
            'actionCards' => $actionCards,
        ]);

        return $this->resolve(['cardId' => $actionCard->getId()]);
    }
}
