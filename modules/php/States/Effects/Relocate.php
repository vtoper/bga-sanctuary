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
use Bga\Games\Sanctuary\Managers\Players;
use Bga\Games\Sanctuary\Managers\Tiles;
use Bga\Games\Sanctuary\Models\Player;
use Bga\Games\Sanctuary\Models\Tile;
use Override;

class Relocate extends ActionStateWithRevert
{
    function __construct(
        protected Game $game,
        protected ?AbstractNode $node = null
    ) {
        parent::__construct(
            $game,
            node: $node,
            id: States::ST_RELOCATE,
            type: StateType::ACTIVE_PLAYER,
            description: clienttranslate('${actplayer} may relocate a tile'),
            descriptionMyTurn: clienttranslate('${you} may relocate a tile'),
        );
    }

    public function getActionArgs(int $activePlayerId): array
    {
        $player = Players::get($activePlayerId);
        $playable = $this->getRelocatableTilesAndLocations($player);

        return [
            '_private' => [
                $player->getId() => [
                    'playableTiles' => $playable,
                    'playableCardsIds' => array_keys($playable)
                ]
            ],
            '_merge_private' => true
        ];
    }

    public function isDoable($player): bool
    {
        $player = $player === null ? Players::getActive() : Players::get($player->getId());
        return !empty($this->getRelocatableTilesAndLocations($player));
    }

    #[Override]
    public function isOptional()
    {
        // relocation is always optional
        return true;
    }

    /**
     * Compute, for each tile on the player's board (excluding starting open areas),
     * the list of locations where it could be relocated.
     *
     * @return array<string, array<array{x:int,y:int}>> map of tile id => list of locations
     */
    protected function getRelocatableTilesAndLocations(Player $player): array
    {
        $map = $player->map();
        $result = [];
        $freeLocations = $map->getAvailableLocations();
        foreach ($map->getTiles() as $tile) {
            // Skip tiles that cannot be relocated
            if ($tile->getState() == 3) {
                // Starting position & starting open areas cannot be relocated
                continue;
            }

            $possibleLocations = [];

            // For release projects, allow placement anywhere that is free
            if ($tile->getType() == Tile::TILE_PROJECT && $tile->isRelease()) {
                foreach ($map->getPlacementOptionsCachedDatas() as $cell) {
                    if (!$map->hasTileAtPos($cell)) {
                        $possibleLocations[] = $cell;
                    }
                }
            } elseif ($tile->getType() == Tile::TILE_BUILDING) {
                $possibleLocations = $map->getPlacementOptions($tile);
            } elseif ($tile->getType() == Tile::TILE_ANIMAL) {
                $animal = $tile;
                if ($animal->getOpenAreas() !== []) {
                    $mandatoryOpenAreas = $animal->getOpenAreas();
                    list($newLocations, $neededOpenAreas) = $map->checkMandatoryOpenAreas($mandatoryOpenAreas, $freeLocations, false);
                    $possibleLocations = $newLocations;
                } else {
                    $possibleLocations = $freeLocations;
                }
            } else {
                // For other tiles, apply normal placement constraints
                $possibleLocations = $freeLocations;
            }

            if (!empty($possibleLocations)) {
                $result[$tile->getId()] = $possibleLocations;
            }
        }

        return $result;
    }

    #[PossibleAction]
    public function actRelocate(string $tileId, string $location)
    {
        $player = Players::getCurrent();
        if ($player != Players::getActive()) {
            throw new UserException(clienttranslate('Not your turn'));
        }

        return $this->relocate($player, $tileId, $location);
    }

    private function relocate(Player $player, string $tileId, string $location)
    {
        $map = $player->map();

        // Validate tile exists and belongs to player
        $tile = Tiles::get($tileId);
        if ($tile === null || $tile->getPId() != $player->getId()) {
            throw new UserException(clienttranslate('Invalid tile'));
        }

        // Cannot relocate starting open areas
        if ($tile->getState() == 3) {
            throw new UserException(clienttranslate('This tile cannot be relocated'));
        }

        $position = $this->parseLocation($location);

        // Get possible locations for this tile
        $playable = $this->getRelocatableTilesAndLocations($player);
        if (!isset($playable[$tileId])) {
            throw new UserException(clienttranslate('This tile cannot be relocated'));
        }

        // Check if location is in the allowed list
        $isLocationAllowed = false;
        foreach ($playable[$tileId] as $cell) {
            if ($cell['x'] == $position['x'] && $cell['y'] == $position['y']) {
                $isLocationAllowed = true;
                break;
            }
        }

        if (!$isLocationAllowed) {
            throw new UserException(clienttranslate('This location is not available'));
        }

        // Remove tile from its current position
        $currentX = $tile->getX();
        $currentY = $tile->getY();

        // Add tile to new position
        [$playedAnimal, $bonuses] = $map->addTile($tileId, $position);
        $this->insertBonusesFlow($bonuses, clienttranslate('placement bonus'));

        // Notify all players
        $this->notify->all('tileRelocated', clienttranslate('${player_name} relocates ${tile_name}'), [
            'player' => $player,
            'tile' => $tile,
            'tile_name' => $tile->getName(),
            'fromX' => $currentX,
            'fromY' => $currentY,
            'toX' => $position['x'],
            'toY' => $position['y'],
            'bonuses' => $bonuses,
            'i18n' => ['tile_name']
        ]);

        return $this->resolve();
    }

    private function parseLocation(string $location): array
    {
        $parts = explode('_', $location);
        if (count($parts) !== 2 || filter_var($parts[0], FILTER_VALIDATE_INT) === false || filter_var($parts[1], FILTER_VALIDATE_INT) === false) {
            throw new UserException(clienttranslate('Invalid location'));
        }

        return ['x' => (int) $parts[0], 'y' => (int) $parts[1]];
    }
}
