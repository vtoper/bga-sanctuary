<?php

declare(strict_types=1);

namespace Bga\Games\Sanctuary\States\Actions;

use Bga\GameFramework\Actions\Types\JsonParam;
use Bga\GameFramework\StateType;
use Bga\GameFramework\States\GameState;
use Bga\GameFramework\States\PossibleAction;
use Bga\GameFramework\UserException;
use Bga\GameFramework\SystemException;
use Bga\Games\Sanctuary\Game;
use Bga\Games\Sanctuary\Constants\States;
use Bga\Games\Sanctuary\Framework\Db\Log;
use Bga\Games\Sanctuary\Framework\Engine\AbstractNode;
use Bga\Games\Sanctuary\Framework\Engine\ActionStateWithRevert;
use Bga\Games\sanctuary\Framework\Models\Player as ModelsPlayer;
use Bga\Games\Sanctuary\Managers\Players;
use Bga\Games\Sanctuary\Managers\Meeples;
use Bga\Games\Sanctuary\Managers\Tiles;
use Bga\Games\Sanctuary\Models\Player;
use Bga\Games\Sanctuary\Models\Tile;
use Bga\Games\Sanctuary\Models\ZooMap;
use Override;

class Conservation extends ActionStateWithRevert
{
    function __construct(
        protected Game $game,
        protected ?AbstractNode $node = null
    ) {
        parent::__construct(
            $game,
            node: $node,
            id: States::ST_CONSERVATION,
            type: StateType::ACTIVE_PLAYER,
            description: clienttranslate('${actplayer} may support a conservation project'),
            descriptionMyTurn: clienttranslate('${you} may support a conservation project'),
        );
    }

    public function getDescription()
    {
        if (!is_null($this->getSource())) {
            return [
                "log" => clienttranslate('Support a conservation project (${source})'),
                "args" => [
                    "source" => $this->getSource() ?? ""
                ]
            ];
        }

        return clienttranslate('Support a conservation project');
    }

    // Building is always optional
    public function isOptional()
    {
        return true;
    }


    public function getActionArgs(int $activePlayerId): array
    {
        $player = Players::get($activePlayerId);
        $args = [
            'source' => $this->getSource(),
            'playableMarkers' => $player->getPlayableAchievementMarkers(),
        ];
        return $args;
    }

    public function isDoable(int|ModelsPlayer $playerId): bool
    {
        $player = $playerId instanceof Player ? $playerId : Players::get($playerId);
        return count($player->getPlayableAchievementMarkers()) > 0;
    }



    #[PossibleAction]
    public function actConservation(int $markerId, string $supported)
    {
        $player = Players::getCurrent();
        if ($player != Players::getActive()) {
            throw new UserException('Not your turn');
        }

        $playableMarkers = $player->getPlayableAchievementMarkers();
        $playableMarker = $playableMarkers[$markerId] ?? null;
        if ($playableMarker === null || !in_array($supported, $playableMarker['achievements'], true)) {
            throw new UserException(clienttranslate('This conservation project cannot be supported'));
        }

        $marker = Meeples::get($markerId);
        if ($marker === null || $marker->getPId() != $player->getId()) {
            throw new UserException(clienttranslate('This conservation marker is not available'));
        }

        $usedConservationMarkers = $playableMarker['conservationMarkers'][$supported] ?? 0;
        Meeples::move($markerId, Meeples::LOCATION_CONSERVATION_BOARD . '-' . $supported);
        if ($usedConservationMarkers > 0) {
            $player->incConservationMarker(-$usedConservationMarkers);
        }

        $this->notify->all('conservationSupported', clienttranslate('${player_name} supports a conservation project'), [
            'player' => $player,
            'player_name' => $player->getName(),
            'marker' => $marker,
            'marker_id' => $marker->getId(),
            'marker_type' => $marker->getType(),
            'supported' => $supported,
            'conservation_markers' => $usedConservationMarkers,
            'remaining_conservation_markers' => $player->getConservationMarker(),
        ]);

        return $this->resolve(['supported' => $supported]);
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
        // Example of zombie level 0: return NextPlayer::class; or $this->actPass($playerId);

        // Example of zombie level 1:
        $args = $this->getArgs($playerId);
        $tileId = $this->getRandomZombieChoice($args['playableCardsIds']);
        $location = $args['playableTiles'][$tileId][0];
        $locationKey = $location['x'] . '_' . $location['y'];
        $openAreas = [];
        foreach ($args['neededOpenAreas'][$tileId][$locationKey] ?? [] as $position) {
            $openArea = Players::get($playerId)->getHand(Tile::TILE_OPEN_AREA)->first();
            if ($openArea === null) {
                throw new UserException('Zombie cannot satisfy the required open area');
            }
            $openAreas[$position['x'] . '_' . $position['y']] = $openArea->getId();
        }
        return $this->actAnimal($tileId, $locationKey, $openAreas, $playerId);
    }
}
