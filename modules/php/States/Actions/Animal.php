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

class Animal extends ActionStateWithRevert
{
    function __construct(
        protected Game $game,
        protected ?AbstractNode $node = null
    ) {
        parent::__construct(
            $game,
            node: $node,
            id: States::ST_PLAY_CARD,
            type: StateType::ACTIVE_PLAYER,
            description: clienttranslate('${actplayer} must play a ${habitat} or undefined animal with max level ${level}'),
            descriptionMyTurn: clienttranslate('${you} must play a ${habitat} or undefined animal with max level ${level}'),
        );
    }

    public function getCustomStateDescription()
    {
        if (!is_null($this->getSource())) {
            return [
                "description" => clienttranslate('${actplayer} must play a ${habitat} or undefined animal with max level ${level} (${source})'),
                "descriptionMyTurn" => clienttranslate('${you} must play a ${habitat} or undefined animal with max level ${level} (${source})'),
            ];
        }
        return null;
    }

    public function getDescription()
    {
        if (!is_null($this->getSource())) {
            return [
                "log" => clienttranslate('Play card (${source})'),
                "args" => [
                    "source" => $this->getSource() ?? ""
                ]
            ];
        }
        if (!is_null($this->getNodeArgs("habitat"))) {
            return [
                "log" => clienttranslate('Play ${habitat}'),
                "args" => [
                    "habitat" => $this->getNodeArgs("habitat", "")
                ]
            ];
        }
        return clienttranslate('Play card');
    }


    public function getActionArgs(int $activePlayerId): array
    {
        $player = Players::get($activePlayerId);
        $playable = $this->getPlayableTilesAndLocations($player);
        $args = [
            'habitat' => $this->getNodeArgs("habitat", ""),
            'level' => $this->getNodeArgs("strength", 1),
            'source' => $this->getSource() ?? "",
            '_private' => [
                $player->getId() => [
                    'playableTiles' => $playable[0],
                    'neededOpenAreas' => $playable[1],
                    'playableCardsIds' => array_keys($playable[0])
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
        $maxStrength = $this->getNodeArgs("strength", 1);
        $habitat = $this->getNodeArgs("habitat", null);
        $map = $player->map();
        $locations = $map->getAvailableLocations();
        if (empty($locations)) {
            return [[], []];
        }

        $result = [];
        $openAreasByTile = [];
        foreach ($player->getHand(Tile::TILE_ANIMAL) as $tileId => $animal) {
            if ($animal->matchesPlayConstraints($maxStrength, $habitat)) {
                $newLocations = $locations;
                $openAreasByTile[$tileId] = [];
                if ($animal->getOpenAreas() !== []) {
                    $mandatoryOpenAreas = $animal->getOpenAreas();
                    list($newLocations, $neededOpenAreas) = $map->checkMandatoryOpenAreas($mandatoryOpenAreas, $locations);
                    $openAreasByTile[$tileId] = $neededOpenAreas;
                }

                $result[$tileId] = $newLocations;
            }
        }
        return [$result, $openAreasByTile];
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
    public function actAnimal(string $tileId, string $location, #[JsonParam(associative: null)] array $openAreas)
    {
        $player = Players::getCurrent();
        if ($player != Players::getActive()) {
            throw new UserException('Not your turn');
        }

        $args = $this->getArgs($player->getId());

        if (!isset($args['playableTiles'][$tileId])) {
            throw new UserException(clienttranslate('This animal cannot be played'));
        }
        $position = $this->parseLocation($location);
        if (!$this->containsCell($args['playableTiles'][$tileId], $position)) {
            throw new UserException(clienttranslate('This location is not available for this animal'));
        }

        $animal = $player->getHand(Tile::TILE_ANIMAL)[$tileId] ?? null;
        if ($animal === null || !$animal->matchesPlayConstraints(
            $this->getNodeArgs('strength', 1),
            $this->getNodeArgs('habitat', null)
        )) {
            throw new UserException(clienttranslate('This animal is not playable'));
        }

        $locationKey = $position['x'] . '_' . $position['y'];
        $requiredOpenAreas = $args['neededOpenAreas'][$tileId][$locationKey] ?? [];
        $openBonuses = $this->processOpenAreas($player, $openAreas, $requiredOpenAreas);

        $map = $player->map();
        [$playedAnimal, $bonuses] = $map->addTile($tileId, $position);
        $bonuses = array_merge($bonuses, $openBonuses);
        $this->insertBonusesFlow($bonuses, clienttranslate('placement bonus'));

        $this->notify->all('animalPlayed', clienttranslate('${player_name} plays ${animal_name}'), [
            'player' => $player,
            'player_name' => $player->getName(),
            'animal' => $playedAnimal,
            'animal_name' => $playedAnimal->getName(),
            'bonuses' => $bonuses,
            'i18n' => ['animal_name'],
        ]);

        // If we placed an animal near it's pair,  player earn a conservation marker
        $map->addConservationMarker($playedAnimal);

        // TODO
        // Effects of the played tile to insert
        // Reactions to insert
        //Tiles::applyEffects($player, 'AnimalPlayed', $effectArgs);

        return $this->resolve(['tileId' => $tileId]);
    }


    private function processOpenAreas(Player $player, array $openAreas, array $required): array
    {
        $bonuses = [];

        $result = [];
        $usedTileIds = [];
        $deleted = [];
        $newTiles = [];
        foreach ($required as $position) {
            $key = $position['x'] . '_' . $position['y'];
            $openAreaBonuses = [];

            // we take the first tile from hand
            $tileId = array_pop($openAreas);
            $tile = $player->getHand()[$tileId] ?? null;
            if ($tile === null) {
                throw new SystemException('Invalid open area tile. Should not happen');
            }
            if (isset($usedTileIds[$tileId])) {
                throw new SystemException(clienttranslate('An open area tile cannot be used twice'));
            }
            $usedTileIds[$tileId] = true;
            [$tile, $openAreaBonuses] = $player->map()->addOpenArea($tileId, $position);
            $bonuses = array_merge($bonuses, $openAreaBonuses);
            $newTiles[] = $tile;
            $deleted[] = $tileId;
        }

        // TODO: notification for new tiles OR return

        return $bonuses;
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
