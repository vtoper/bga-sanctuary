<?php

declare(strict_types=1);

namespace Bga\Games\Sanctuary\States\Actions;

use Bga\GameFramework\StateType;
use Bga\GameFramework\States\GameState;
use Bga\GameFramework\States\PossibleAction;
use Bga\GameFramework\UserException;
use Bga\Games\Sanctuary\Game;
use Bga\Games\Sanctuary\Constants\States;
use Bga\Games\Sanctuary\Managers\Players;
use Bga\Games\Sanctuary\Managers\ActionCards;
use Bga\Games\Sanctuary\Framework\Db\Log;
use Bga\Games\Sanctuary\Framework\Engine\AbstractNode;
use Bga\Games\Sanctuary\Framework\Engine\ActionStateWithRevert;
use Bga\Games\Sanctuary\Framework\Engine\Engine;
use Bga\Games\Sanctuary\Managers\Globals;

class ChooseActionCard extends ActionStateWithRevert
{
    function __construct(
        protected Game $game,
        protected ?AbstractNode $node = null
    ) {
        parent::__construct(
            $game,
            node: $node,
            id: States::ST_CHOOSE_ACTION_CARD,
            type: StateType::ACTIVE_PLAYER,
            description: clienttranslate('${actplayer} must choose an action card'),
            descriptionMyTurn: clienttranslate('${you} must choose an action card'),
        );
    }

    public function getDescription()
    {
        if (!is_null($this->getNodeArgs("sourceName"))) {
            return [
                "log" => clienttranslate('Choose an action card (${sourceName})'),
                "args" => [
                    "sourceName" => $this->getNodeArgs("sourceName", "")
                ]
            ];
        }
        return clienttranslate('Choose an action card');
    }


    public function getActionArgs(int $activePlayerId): array
    {
        $player = Players::getActive();
        $forcedCardId = $this->getNodeArgs('cardId', null);
        $forcedStrength = $this->getNodeArgs('strength', null);

        // What action cards are we talking about
        if (!is_null($forcedCardId)) {
            // This case is used for Multiplier and animal effect Action
            $cards = ActionCards::getMany([$forcedCardId]);
        } else {
            $cards = $player->getActionCards();
        }

        $data = [
            'strengths' => $cards->map(function ($card) {
                return $card->getCurrentStrength();
            }),
        ];


        if (!is_null($forcedCardId)) {
            $card = ActionCards::getSingle($forcedCardId);
            $data['descSuffix'] = 'action';
            $data['type'] = $card->getType();
            $data['i18n'][] = 'type';
        }

        return $data;
    }

    #[PossibleAction]
    public function actChooseActionCard($cardId)
    {
        $player = Players::getActive();
        $args = $this->getArgs();

        if (!isset($args['cards'][$cardId])) {
            throw new \BgaVisibleSystemException('Card action not doable. Should not happen');
        }

        // Activate the card
        $card = ActionCards::get($cardId);
        $card->setStatus(1);

        // Notify
        $this->notify->all(
            'chooseActionCard',
            clienttranslate(
                '${player_name} chooses action card ${action_card_name}${action_card_icon}${action_card_level}'
            ),
            [
                'player' => $player,
                'actionCard' => $card,
            ]
        );
        // Do action
        $flow = $card->getTaggedFlow($player);
        Engine::insertAsChild($flow);
        // After finishing flow 
        $afterFlow = $card->getAfterFinishingTaggedFlow($player);
        if (!empty($afterFlow)) {
            Engine::pushAfterFinishingChilds([$afterFlow]);
        }

        $methodName = 'incAction' . $card->getName();
        // Stats::$methodName($player);
        Globals::setActiveActionCard([
            'type' => $card->getType(),
            'pId' => $card->getPId(),
            'lvl' => $card->getLevel(),
        ]);

        // Insert cleanup actionName
        Engine::insertAsChild([
            'action' => States::ST_CLEANUP,
            'pId' => $player->getId(),
            'args' => ['card' => $cardId],
        ]);
        $this->resolve(['card' => $cardId]);
    }
}
