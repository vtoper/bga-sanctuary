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
use Bga\Games\Sanctuary\Framework\Engine\ActionStateWithRevert;
use Bga\Games\Sanctuary\Managers\Players;
use Bga\Games\Sanctuary\Managers\Tiles;

class TakeTile extends ActionStateWithRevert
{
    function __construct(
        protected Game $game,
        protected ?AbstractNode $node = null
    ) {
        parent::__construct(
            $game,
            node: $node,
            id: States::ST_TAKE_TILE,
            type: StateType::ACTIVE_PLAYER,
            description: clienttranslate('${actplayer} must take ${n} tile in range'),
            descriptionMyTurn: clienttranslate('${you} must take ${n} tile in range'),
        );
    }

    public function getActionArgs(int $activePlayerId): array
    {
        $player = Players::getActive();
        $inRange = $this->getNodeArgs("inRange", false);
        $n = $this->getNodeArgs("max", 1);
        return [
            "n" => $n,
            "inRange" => $inRange,
            "source" => $this->getSource() ?? "",
            'cardIds' => $inRange ? $player->getTilesInReputationRange()->getIds() : Tiles::getPool()->getIds(),
            "taken" => $this->getNodeArgs("taken", 0),
        ];
    }

    public function getCustomStateDescription()
    {
        if (!is_null($this->getSource())) {
            if ($this->getNodeArgs('inRange', false)) {
                return [
                    "description" => clienttranslate('${actplayer} must take ${n} tile (${source})'),
                    "descriptionMyTurn" => clienttranslate('${you} must take ${n} tile (${source})'),
                ];
            }
            return [
                "description" => clienttranslate('${actplayer} must take ${n} tile in range (${source})'),
                "descriptionMyTurn" => clienttranslate('${you} must take ${n} tile in range (${source})'),
            ];
        }
        return null;
    }

    public function getDescription()
    {
        if (!is_null($this->getSource())) {
            if ($this->getNodeArgs('inRange', false)) {
                return [
                    "log" => clienttranslate('Take ${n} tile (${source})'),
                    "args" => [
                        "source" => $this->getSource() ?? "",
                        "n" => $this->getNodeArgs("max", 1)
                    ]
                ];
            }
            return [
                "log" => clienttranslate('Take ${n} tile in range (${source})'),
                "args" => [
                    "source" => $this->getSource() ?? "",
                    "n" => $this->getNodeArgs("max", 1)
                ]
            ];
        }
        if ($this->getNodeArgs('inRange', false)) {
            return [
                "log" => clienttranslate('Take ${n} tile'),
                "args" => [
                    "n" => $this->getNodeArgs("max", 1)
                ]
            ];
        }
        return [
            "log" => clienttranslate('Take ${n} tile in range'),
            "args" => [
                "n" => $this->getNodeArgs("max", 1)
            ]
        ];
    }

    public function onEnteringState(int $activePlayerId)
    {
        $args = $this->getActionArgs($activePlayerId);
        // Only one tile to take, do it automatically
        if (count($args['cardIds']) == 1) {
            return $this->actTakeTile($args['cardIds']);
        }
    }

    #[PossibleAction]
    public function actTakeTile($cardIds)
    {
        $player = Players::getActive();
        $args = $this->getActionArgs($player->getId());
        foreach ($cardIds as $cardId) {
            if (!in_array($cardId, $args['cardIds'])) {
                throw new \BgaVisibleSystemException('This card cannot be taken. Should not happen');
            }
        }

        // move cards
        $tiles = [];
        foreach ($cardIds as $cardId) {
            $tile = Tiles::getSingle($cardId);
            $tile = Tiles::addToHand($cardId, $player);
            $tiles[] = $tile;
        }

        $msg = clienttranslate('${player_name} takes ${card_names} in reputation range from the display');
        if (!$args['inRange']) {
            $msg = clienttranslate('${player_name} takes ${card_names} from the display');
        }

        $this->notify->all('takeTiles', $msg, [
            'player' => $player,
            'cards' => $tiles,
        ]);

        return $this->resolve(["n" => count($cardIds)]);
    }

    function zombie(int $playerId)
    {
        // Example of zombie level 0: return NextPlayer::class; or $this->actPass($playerId);

        // Example of zombie level 1:
        $args = $this->getArgs($playerId);
        $zombieChoice = $this->getRandomZombieChoice($args['cardIds']); // random choice over possible moves
        return $this->actTakeTile($zombieChoice); // this function will return the transition to the next state
    }
}
