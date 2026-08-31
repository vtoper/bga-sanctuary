<?php

declare(strict_types=1);

namespace Bga\Games\Sanctuary\States\Effects;

use Bga\Games\Sanctuary\Managers\Globals;
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
use Bga\Games\Sanctuary\Models\Tile;


class Hunter extends ActionStateWithRevert
{
    function __construct(
        protected Game $game,
        protected ?AbstractNode $node = null
    ) {
        parent::__construct(
            $game,
            node: $node,
            id: States::ST_HUNTER,
            type: StateType::ACTIVE_PLAYER,
            description: clienttranslate('${actplayer} must keep 1 Animal card from the ${n} drawn cards'),
            descriptionMyTurn: clienttranslate('${you} must keep 1 Animal card from the ${n} drawn cards'),
        );
    }

    public function getActionArgs(int $activePlayerId): array
    {
        $player = Players::getActive();
        $cards = Tiles::getMany(Globals::getEffectHunter());
        $animals = $cards->filter(function ($card) {
            return $card->getType() == Tile::TILE_ANIMAL;
        });

        return [
            'n' => $this->getNodeArgs('n', 1),
            '_private' => [
                'active' => [
                    'cardIds' => $animals->getIds(),
                ],
            ],
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
        $player = Players::get($activePlayerId);
        $drawnTiles = Tiles::draw($player, $this->getNodeArgs('n', 1));
        Globals::setEffectHunter($drawnTiles->getIds());

        $args = $this->getActionArgs($activePlayerId);
        $animalIds = $args['_private']['active']['cardIds'];
        if (count($animalIds) === 0) {
            return $this->finishHunter();
        }
        if (count($animalIds) === 1) {
            return $this->actHunter($animalIds);
        }
    }

    #[PossibleAction]
    public function actHunter(#[JsonParam(associative: null)] array $cardIds)
    {
        $player = Players::getActive();
        $args = $this->getActionArgs($player->getId());
        $animalIds = $args['_private']['active']['cardIds'];
        if (count($cardIds) !== 1 || count(array_unique($cardIds)) !== count($cardIds)) {
            throw new UserException('You must keep exactly one Animal tile');
        }
        foreach ($cardIds as $cardId) {
            if (!in_array($cardId, $animalIds, true)) {
                throw new \BgaVisibleSystemException('This tile cannot be kept. Should not happen');
            }
        }

        $keptTile = Tiles::getSingle($cardIds[0]);
        return $this->finishHunter($keptTile);
    }

    private function finishHunter(?Tile $keptTile = null)
    {
        $player = Players::getActive();
        $cardIdsToDiscard = Globals::getEffectHunter();
        if ($keptTile !== null) {
            $cardIdsToDiscard = array_diff($cardIdsToDiscard, [$keptTile->getId()]);
        }

        [$discardedTiles] = Tiles::discard($cardIdsToDiscard);
        Globals::setEffectHunter([]);

        if ($keptTile === null) {
            Tiles::notificationDiscardCards($player, $discardedTiles, null, clienttranslate('${player_name} discards ${card_names} because no Animal tile was drawn'), [
                'player' => $player,
                'cards' => $discardedTiles,
            ]);
        } else {
            $noDiscard = count($discardedTiles) == 0;
            Tiles::notificationDiscardCards(
                $player,
                $discardedTiles,
                $noDiscard
                    ? clienttranslate('You keep ${card_name} for hunter effect')
                    : clienttranslate('You keep ${card_name} and discard ${card_names} for hunter effect'),
                $noDiscard
                    ? clienttranslate('${player_name} keeps ${card_name} card for hunter effect')
                    : clienttranslate('${player_name} keeps ${card_name} card and discard ${n} card(s) for hunter effect'),
                ['card' => $keptTile],
                ['card' => $keptTile]
            );
        }

        return $this->resolve(['n' => $keptTile === null ? 0 : 1]);
    }

    function zombie(int $playerId)
    {
        // Example of zombie level 0: return NextPlayer::class; or $this->actPass($playerId);

        // Example of zombie level 1:
        $args = $this->getActionArgs($playerId);
        $animalIds = $args['_private']['active']['cardIds'];
        if (count($animalIds) === 0) {
            return $this->finishHunter();
        }
        $zombieChoice = $this->getRandomZombieChoice($animalIds);
        return $this->actHunter([$zombieChoice]);
    }
}
