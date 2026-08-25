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
use Bga\Games\Sanctuary\Managers\Players;
use Bga\Games\Sanctuary\Managers\Tiles;
use Bga\Games\Sanctuary\Models\Player;
use Bga\Games\Sanctuary\Models\Tile;
use Bga\Games\Sanctuary\Models\ZooMap;

class Building extends ActionStateWithRevert
{
    function __construct(
        protected Game $game,
        protected ?AbstractNode $node = null
    ) {
        parent::__construct(
            $game,
            node: $node,
            id: States::ST_BUILDING,
            type: StateType::ACTIVE_PLAYER,
            description: clienttranslate('${actplayer} may play a building'),
            descriptionMyTurn: clienttranslate('${you} may play a building'),
        );
    }

    public function getDescription()
    {
        if (!is_null($this->getSource())) {
            return [
                "log" => clienttranslate('Play a building (${source})'),
                "args" => [
                    "source" => $this->getSource() ?? ""
                ]
            ];
        }

        return clienttranslate('Play a building');
    }

    // Building is always optional
    public function isOptional()
    {
        return true;
    }


    public function getActionArgs(int $activePlayerId): array
    {
        $player = Players::get($activePlayerId);
        $playable = $this->getPlayableTilesAndLocations($player);
        $args = [
            'source' => $this->getSource(),
            '_private' => [
                $player->getId() => [
                    'playableTiles' => $playable,
                    'playableCardsIds' => array_keys($playable)
                ]
            ],
            '_merge_private' => true
        ];
        return $args;
    }

    /**
     * Compute, for each animal tile in the player's hand that satisfies the strength/habitat constraints,
     * the list of locations on the ZooMap where it could be placed.
     *
     * @return array<string, array<array{x:int,y:int}>> map of tile id => list of locations
     */
    protected function getPlayableTilesAndLocations(Player $player): array
    {
        $map = $player->map();
        $locations = $map->getAvailableLocations();
        if (empty($locations)) {
            return [];
        }

        $result = [];
        $openAreasByTile = [];
        foreach ($player->getHand(Tile::TILE_BUILDING) as $tileId => $building) {
            $possible = $map->getPlacementOptions($building);
            if (!empty($possible)) {
                $result[$tileId] = $possible;
            }
        }
        return $result;
    }


    private function parseLocation(string $location): array
    {
        $parts = explode('_', $location);
        if (count($parts) !== 2 || filter_var($parts[0], FILTER_VALIDATE_INT) === false || filter_var($parts[1], FILTER_VALIDATE_INT) === false) {
            throw new UserException(clienttranslate('Invalid location'));
        }
        return ['x' => (int) $parts[0], 'y' => (int) $parts[1]];
    }

    private function containsCell(array $locations, array $needle): bool
    {
        foreach ($locations as $location) {
            if ((int) $location['x'] === $needle['x'] && (int) $location['y'] === $needle['y']) {
                return true;
            }
        }
        return false;
    }

    #[PossibleAction]
    public function actBuilding(string $tileId, string $location)
    {
        $player = Players::getCurrent();
        if ($player != Players::getActive()) {
            throw new UserException('Not your turn');
        }

        $args = $this->getArgs($player->getId());
        $playableTiles = $this->getPlayableTilesAndLocations($player);
        if (!isset($playableTiles[$tileId])) {
            throw new UserException('This building cannot be played. Should not happen');
        }
        $position = $this->parseLocation($location);
        if (!$this->containsCell($playableTiles[$tileId], $position)) {
            throw new UserException('This location is not available for this building. Should not happen');
        }

        $building = Tiles::get($tileId) ?? null;

        $locationKey = $position['x'] . '_' . $position['y'];

        $map = $player->map();
        [$playedBuilding, $bonuses] = $map->addTile($tileId, $position);

        $this->insertBonusesFlow($bonuses, clienttranslate('placement bonus'));

        $this->notify->all('buildingPlayed', clienttranslate('${player_name} plays ${building_name}'), [
            'player' => $player,
            'player_name' => $player->getName(),
            'building' => $playedBuilding,
            'building_name' => $playedBuilding->getName(),
            'bonuses' => $bonuses,
            'i18n' => ['building_name'],
        ]);

        // TODO
        // Effects of the played tile to insert
        // Bonuses to insert
        // Reactions to insert
        //Tiles::applyEffects($player, 'AnimalPlayed', $effectArgs);
        // 

        return $this->resolve(['building', 'tileId' => $tileId]);
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
