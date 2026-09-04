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
use Bga\GameFramework\Actions\Types\JsonParam;
use Bga\Games\Sanctuary\Constants\Icons;
use Bga\Games\Sanctuary\Models\Tile;

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
        $typeFilter = $this->getNodeArgs("typeFilter", null);
        $n = $this->getNodeArgs("max", 1);
        $cards = $inRange ? $player->getTilesInReputationRange() : Tiles::getPool();
        if ($typeFilter !== null) {
            $cards = $cards->filter(function ($card) use ($typeFilter) {
                if ($typeFilter == Icons::SMALL_ANIMALS) {
                    return $card->getType() == Tile::TILE_ANIMAL && in_array($card->getStrength(), [2, 3]);
                }
                if ($typeFilter == Icons::LARGE_ANIMALS) {
                    return $card->getType() == Tile::TILE_ANIMAL && in_array($card->getStrength(), [4, 5]);
                }
                if (in_array($typeFilter, Icons::CONTINENTS)) {
                    return in_array($typeFilter, $card->getContinents());
                }
                return $card->getType() == $typeFilter;
            });
        }
        return [
            "n" => $n,
            "inRange" => $inRange,
            "source" => $this->getSource() ?? "",
            'cardIds' => $cards->getIds(),
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
        if (count($args['cardIds']) > 0 && count($args['cardIds']) <= $this->getNodeArgs("max", 1)) {
            return $this->actTakeTile($args['cardIds']);
        }
    }

    #[PossibleAction]
    public function actTakeTile(#[JsonParam(associative: null)] array $cardIds)
    {
        $player = Players::getActive();
        $args = $this->getActionArgs($player->getId());
        if (!is_array($cardIds) || (count($cardIds) != $args['n'] && $args['n'] != 99) || count(array_unique($cardIds)) !== count($cardIds)) {
            throw new \Bga\GameFramework\UserException('You must take the required number of tiles');
        }
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
        $args = $this->getArgs($playerId);
        $zombieChoice = $this->getRandomZombieChoice($args['cardIds']); // random choice over possible moves
        return $this->actTakeTile($zombieChoice); // this function will return the transition to the next state
    }
}
