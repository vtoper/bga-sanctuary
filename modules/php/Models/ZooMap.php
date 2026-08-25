<?php


namespace Bga\Games\sanctuary\Models;

use Bga\Games\sanctuary\Managers\Players;
use Bga\Games\sanctuary\Managers\Globals;
use Bga\Games\sanctuary\Managers\Meeples;
use Bga\Games\sanctuary\Managers\Tiles;
use Bga\Games\Sanctuary\Constants\Effects;
use Bga\Games\Sanctuary\Constants\Icons;
use Bga\Games\Sanctuary\Constants\Prerequisites;

/*
 * ZooMap: all utility functions concerning a Zoo Map
 */

class ZooMap
{
  // STATIC DATA
  protected $id = '';
  protected $asset = null;
  protected $name = '';
  protected $desc = '';
  protected $bonuses = [];
  protected $startingOpenAreas = [];
  public const DIRECTIONS = [
    "NW" => ['x' => -1, 'y' => -1],
    "N" => ['x' => 0, 'y' => -2],
    "NE" => ['x' => 1, 'y' => -1],
    "SE" => ['x' => 1, 'y' => 1],
    "S" => ['x' => 0, 'y' => 2],
    "SW" => ['x' => -1, 'y' => 1],
  ];

  // CONSTRUCT
  protected $player = null;
  protected $pId = null;
  public function __construct($player = null)
  {
    if (!is_null($player)) {
      $this->player = $player;
      $this->pId = $player->getId();
      $this->fetchDatas();
    }
  }

  public function canUseEffect()
  {
    return true;
  }


  public function getId()
  {
    return $this->id;
  }

  public function getName()
  {
    return $this->name;
  }


  public function getBonuses()
  {
    return $this->bonuses;
  }


  public function getUiData()
  {
    return [
      'id' => $this->id,
      'name' => $this->name,
      'desc' => $this->desc,
      'asset' => $this->asset ?? $this->id,
      'bonuses' => $this->bonuses,
    ];
  }

  public function setupPlayer(Player $player): null
  {
    $this->player = $player;
    $this->pId = $player->getId();
    $openAreas = [];
    // creating the open areas
    foreach ($this->startingOpenAreas as $cell) {
      $openAreas[] = [
        'id' => 'openArea_' . $player->getId() . '_' . $cell['x'],
        'player_id' => $player->getId(),
        'location' => 'board',
        'x' => $cell['x'],
        'y' => $cell['y'],
        'state' => 3 // cannot be selected for placement
      ];
    }
    $openAreas[] = [
      'id' => 'startingPosition_' . $player->getId(),
      'player_id' => $player->getId(),
      'location' => 'board',
      'x' => 3,
      'y' => 6,
      'state' => 0 // cannot be selected for placement
    ];
    Tiles::create($openAreas, null);
    return null;
  }

  public function getStatus()
  {
    return null;
  }

  public function refresh()
  {
    $this->fetchDatas();
  }

  /**
   * Fetch DB for tiles and fill the grid
   */
  protected $grid = [];
  protected $tiles = null;
  protected function fetchDatas()
  {
    if ($this->player == null) {
      return;
    }

    $this->grid = self::createGrid();
    foreach ($this->grid as $x => $col) {
      foreach ($col as $y => $cell) {
        $this->grid[$x][$y] = [
          'tile' => null,
        ];
      }
    }

    // foreach ($this->startingOpenAreas as $cell) {
    //   $this->grid[$cell['x']][$cell['y']]['tile'] = [
    //     'x' => $cell['x'],
    //     'y' => $cell['y'],
    //     'id' => 'openArea_' . $cell['x'] . '_' . $cell['y'],
    //   ];
    // }

    $this->tiles = Tiles::getOfPlayer($this->pId, 'board');
    foreach ($this->tiles as $tile) {
      $this->grid[$tile->getX()][$tile->getY()]['tile'] = $tile;
    }
  }

  ///////////////////////////////////////////////
  // ████████╗ ██╗██╗     ███████╗███████╗
  // ╚══██╔══╝ ██║██║     ██╔════╝██╔════╝
  //    ██║    ██║██║     █████╗  ███████╗
  //    ██║    ██║██║     ██╔══╝  ╚════██║
  //    ██║    ██║███████╗███████╗███████║
  //    ╚═╝    ╚═╝╚══════╝╚══════╝╚══════╝                                 
  ///////////////////////////////////////////////
  public function getTiles()
  {
    return $this->tiles;
  }

  public function addTile($tileId, $pos)
  {
    $tile = Tiles::addToMap($tileId, $this->pId, $pos);

    return $this->addBuildingAux($tile);
  }

  public function replaceTile($tileId, $pos)
  {
    $existingTile = $this->grid[$pos['x']][$pos['y']]['tile'] ?? null;
    $existingTile->setLocation('released');
    Tiles::addToMap($tileId, $this->pId, $pos);
    return $existingTile;
  }

  public function addOpenArea($tileId, $pos)
  {
    Tiles::delete($tileId);
    $newTile = Tiles::singleCreate([
      'id' => 'openArea_' . $this->pId . '_' . $pos['x'] . '_' . $pos['y'],
      'player_id' => $this->pId,
      'location' => 'board',
      'x' => $pos['x'],
      'y' => $pos['y'],
      'state' => 0, // cannot be selected for placement
    ]);
    $newTile = Tiles::addToMap($newTile->getId(), $this->pId, $pos);
    return [$newTile, $this->addBuildingAux($newTile)[1]];
  }

  protected function addBuildingAux(Tile $tile, $isRepositioning = false, $previousBonuses = [])
  {

    $this->tiles[$tile->getId()] = &$tile;
    $bonuses = [];
    $this->invalidateCachedDatas();

    $cell = ['x' => $tile->getX(), 'y' => $tile->getY()];
    if (!$this->isCellValid($cell)) {
      throw new \Bga\GameFramework\UserException('Invalid tile location');
    }

    // Animal and open-area tiles are single-cell tiles. A cell must be empty.
    if ($this->hasTileAtPos($cell) && !$isRepositioning) {
      throw new \Bga\GameFramework\UserException('This map location is occupied');
    }

    $this->grid[$cell['x']][$cell['y']]['tile'] = $tile;
    $uid = self::getCellId($cell);
    foreach ($this->bonuses[$uid] ?? [] as $bonus => $n) {
      $bonuses[] = [$bonus => $n];
    }

    return [$tile, $bonuses];
  }

  public function getAdjacentPair(Tile $tile): ?Tile
  {
    foreach ($this->getNeighbours(['x' => $tile->getX(), 'y' => $tile->getY()]) as $cell) {
      $neighbour = $this->getTileAtPos($cell);
      if ($neighbour instanceof \Bga\Games\sanctuary\Models\Animal && $neighbour->getId() === $tile->getPair()) {
        return $neighbour;
      }
    }
    return null;
  }

  public function addConservationMarker(Tile $tile): ?\Bga\Games\sanctuary\Models\Meeple
  {
    if ($this->getAdjacentPair($tile) === null) {
      return null;
    }

    // a pair was played we create a conservation marker
    $tile->getPlayer()->incConservationMarker();
    return null;
  }

  public function getTileAtPos($hex)
  {
    return $this->grid[$hex['x']][$hex['y']]['tile'] ?? null;
  }

  public function hasTileAtPos($hex)
  {
    return !is_null($this->getTileAtPos($hex));
  }

  /**
   * Positions of the tiles actually played by the player
   */
  protected function getPlayedTilePositions($excludeStartOpenAreas = true): array
  {
    $positions = [];
    foreach ($this->tiles as $tile) {
      if ($tile->getState() == 3) {
        continue; // ignore starting open areas
      }
      $positions[] = ['x' => $tile->getX(), 'y' => $tile->getY()];
    }
    return $positions;
  }

  /**
   * Locations available to place a new tile: cells adjacent to already played tiles, or,
   * if none has been played yet, cells adjacent to the starting open areas.
   */
  public function getAvailableLocations(): array
  {
    $anchors = $this->getPlayedTilePositions();
    $locations = [];
    $seen = [];
    foreach ($anchors as $anchor) {
      foreach ($this->getNeighbours($anchor) as $cell) {
        $uid = self::getCellId($cell);
        if (isset($seen[$uid]) || $this->hasTileAtPos($cell)) {
          continue;
        }
        $seen[$uid] = true;
        $locations[] = $cell;
      }
    }
    return $locations;
  }

  public function checkMandatoryOpenAreas($mandatoryOpenAreas, $locations): array
  {
    $newLocations = [];
    $neededOpenAreas = [];
    foreach ($locations as $loc) {
      foreach ($mandatoryOpenAreas as $direction) {
        $dir = self::DIRECTIONS[$direction];
        $adjacentCell = ['x' => $loc['x'] + $dir['x'], 'y' => $loc['y'] + $dir['y']];
        if (!$this->isCellValid($adjacentCell)) {
          continue 2; // skip this location not valid
        }
        if ($this->hasTileAtPos($adjacentCell) && !$this->getTileAtPos($adjacentCell)->isOpenArea()) {
          continue 2; // skip this location, it doesn't satisfy the mandatory open area condition
        }

        if (!$this->hasTileAtPos($adjacentCell)) {
          $neededOpenAreas[$loc['x'] . '_' . $loc['y']][] = $adjacentCell;
        }
      }
      $newLocations[] = $loc;
    }
    return [$newLocations, $neededOpenAreas];
  }

  public function getOpenAreasToPlace($locations, $animal) {}


  /*************************OLD ARKNOVA CODE *****************/

  public function hasBuilding($buildingType)
  {
    return $this->getBuildingOfType($buildingType) !== null;
  }

  protected function getBuildingsNeighbourCells()
  {
    $cells = [];
    foreach (self::getListOfCells() as $cell) {
      if (!is_null($this->getTileAtPos($cell))) {
        $cells = array_merge($cells, $this->getNeighbours($cell));
      }
    }
    return Utils::uniqueZones($cells);
  }

  public function isTileAdjacentTo($tile, $cell)
  {
    $neighbours = [];
    $neighbours = $this->getNeighbours($tile);
    return !empty(Utils::intersectZones([$cell], $neighbours));
  }

  protected $checkingCells = null;
  protected $freeCells = null;
  public function getPlacementOptionsCachedDatas()
  {

    if (is_null($this->freeCells)) {
      $this->freeCells = self::getAvailableLocations();
    }

    return $this->freeCells;
  }
  public function invalidateCachedDatas()
  {
    $this->checkingCells = null;
    $this->freeCells = null;
  }

  public function getPlacementOptions($building, $checkIsDoable = false, $args = [])
  {
    $freeCells = $this->getPlacementOptionsCachedDatas();
    $prerequisites = $building->getPrerequisites();

    $result = [];
    // For each possible cell check if the prerequisites are satisfied
    foreach ($freeCells as $pos) {
      if (!$this->arePrerequisitesSatisfied($prerequisites, $pos)) {
        continue;
      }

      $result[] = $pos;
      if ($checkIsDoable) {
        break;
      }
    }
    return $checkIsDoable ? !empty($result) : $result;
  }

  /**
   * arePrerequisitesSatisfied: checks that $pos (and/or the player's zoo) satisfies all $prerequisites
   */
  protected function arePrerequisitesSatisfied($prerequisites, $pos): bool
  {
    foreach ($prerequisites ?? [] as $key => $amount) {
      if (!$this->isPrerequisiteSatisfied($key, $amount, $pos)) {
        return false;
      }
    }
    return true;
  }

  protected function isPrerequisiteSatisfied($key, $amount, $pos): bool
  {
    switch ($key) {
      case Prerequisites::BY_THE_RIVER:
        return $this->isRiverCell($pos);

      case Prerequisites::NEXT_TO_BUILDINGS:
        return $this->countNeighbourTilesOfType($pos, Tile::TILE_BUILDING) >= $amount;

      case Prerequisites::NEXT_TO_PROJECTS:
        return $this->countNeighbourTilesOfType($pos, Tile::TILE_PROJECT) >= $amount;

      case Prerequisites::NEXT_TO_OPEN_AREAS:
        return $this->countNeighbourMatching($pos, fn($tile) => $tile->isOpenArea()) >= $amount;

      case Prerequisites::NEXT_TO_LARGE_ANIMALS:
        return $this->countNeighbourMatching(
          $pos,
          fn($tile) => $tile instanceof Animal && $tile->getStrength() >= Prerequisites::LARGE_ANIMAL_STRENGTH
        ) >= $amount;

      case Prerequisites::NEXT_TO_DIFFERENT_ANIMAL_OR_CONTINENT_ICONS:
        return count(array_intersect_key($this->getNeighbourIcons($pos), array_flip(Icons::CONTINENTS_AND_TYPES))) >= $amount;

      case Prerequisites::HAVE_DIFFERENT_CONTINENT_ICONS:
        return count(array_intersect_key($this->getZooIcons(), array_flip(Icons::CONTINENTS))) >= $amount;

      case Prerequisites::HAVE_DIFFERENT_ANIMAL_ICONS:
        return count(array_intersect_key($this->getZooIcons(), array_flip(Icons::ANIMAL_TYPES))) >= $amount;

      case Prerequisites::HAVE_DIFFERENT_ANIMAL_AND_CONTINENT_ICONS:
        return count(array_intersect_key($this->getZooIcons(), array_flip(Icons::CONTINENTS_AND_TYPES))) >= $amount;

      case Prerequisites::HAVE_ALL_RIVER_SPACES_FILLED:
        return $this->areAllRiverSpacesFilled();

      case Prerequisites::HAVE_TILES_IN_HAND:
        return $this->player->getHand()->count() >= $amount;

      default:
        // 'CONNECT_<icon>' => next to N tiles of <icon> connected to each other
        if (str_starts_with($key, Prerequisites::CONNECT_PREFIX)) {
          $icon = substr($key, strlen(Prerequisites::CONNECT_PREFIX));
          return $this->getMaxConnectedNeighbourGroupSize($pos, $icon) >= $amount;
        }
        // '<icon>' => N of that icon among the neighbours
        $neighbourIcons = $this->getNeighbourIcons($pos);
        return ($neighbourIcons[$key] ?? 0) >= $amount;
    }
  }

  /**
   * isRiverCell: the river runs along the left and right edges of the zoo map
   */
  protected function isRiverCell($pos): bool
  {
    return $pos['x'] == 0 || $pos['x'] == 6;
  }

  protected function areAllRiverSpacesFilled(): bool
  {
    foreach (self::getListOfCells() as $cell) {
      if ($this->isRiverCell($cell) && !$this->hasTileAtPos($cell)) {
        return false;
      }
    }
    return true;
  }

  /**
   * getNeighbourIcons: aggregate the icons of all tiles adjacent to $pos
   */
  protected function getNeighbourIcons($pos): array
  {
    return $this->aggregateIcons($this->getNeighbours($pos));
  }

  /**
   * getZooIcons: aggregate the icons of every tile played in the player's zoo
   */
  protected function getZooIcons(): array
  {
    $icons = [];
    foreach ($this->tiles as $tile) {
      foreach ($tile->getIcons() as $icon => $n) {
        $icons[$icon] = ($icons[$icon] ?? 0) + $n;
      }
    }
    return $icons;
  }

  protected function aggregateIcons(array $cells): array
  {
    $icons = [];
    foreach ($cells as $cell) {
      $tile = $this->getTileAtPos($cell);
      if (is_null($tile)) {
        continue;
      }
      foreach ($tile->getIcons() as $icon => $n) {
        $icons[$icon] = ($icons[$icon] ?? 0) + $n;
      }
    }
    return $icons;
  }

  protected function countNeighbourTilesOfType($pos, $type): int
  {
    return $this->countNeighbourMatching($pos, fn($tile) => $tile->getType() == $type);
  }

  protected function countNeighbourMatching($pos, callable $predicate): int
  {
    $n = 0;
    foreach ($this->getNeighbours($pos) as $cell) {
      $tile = $this->getTileAtPos($cell);
      if (!is_null($tile) && $predicate($tile)) {
        $n++;
      }
    }
    return $n;
  }

  /**
   * getMaxConnectedNeighbourGroupSize: size of the largest group of tiles carrying $icon, connected to each other,
   * that is adjacent to $pos
   */
  protected function getMaxConnectedNeighbourGroupSize($pos, $icon): int
  {
    $seen = [];
    $max = 0;
    foreach ($this->getNeighbours($pos) as $cell) {
      $uid = self::getCellId($cell);
      if (isset($seen[$uid]) || !$this->cellHasIcon($cell, $icon)) {
        continue;
      }

      $component = $this->getConnectedIconGroup($cell, $icon);
      $seen += $component;
      $max = max($max, count($component));
    }
    return $max;
  }

  /**
   * getConnectedIconGroup: flood-fill the connected group of tiles carrying $icon, starting from $start
   * Returns a map of cell uid => true
   */
  protected function getConnectedIconGroup($start, $icon): array
  {
    $group = [self::getCellId($start) => true];
    $stack = [$start];
    while (!empty($stack)) {
      $cell = array_pop($stack);
      foreach ($this->getNeighbours($cell) as $neighbour) {
        $uid = self::getCellId($neighbour);
        if (isset($group[$uid]) || !$this->cellHasIcon($neighbour, $icon)) {
          continue;
        }
        $group[$uid] = true;
        $stack[] = $neighbour;
      }
    }
    return $group;
  }

  protected function cellHasIcon($cell, $icon): bool
  {
    $tile = $this->getTileAtPos($cell);
    return !is_null($tile) && ($tile->getIcons()[$icon] ?? 0) > 0;
  }

  // Release project must be placed a top an animal card with the corresponding icons
  public function getProjectReleaseOptions(Project $project, $checkIsDoable = false, $args = [])
  {

    $prerequisite = $project->getReleaseIcon();

    $result = [];
    // For each possible tiles check if the prerequisites are satisfied
    foreach ($this->tiles as $tileId => $tile) {
      if ($tile->getType() != Tile::TILE_ANIMAL) {
        continue;
      }
      if (!in_array($prerequisite, array_keys($tile->getIcons()))) {
        continue;
      }
      $result[] = ['x' => $tile->getX(), 'y' => $tile->getY()];
      if ($checkIsDoable) {
        break;
      }
    }
    return $checkIsDoable ? !empty($result) : $result;
  }

  /**
   * getCoveredHexes: given a building type, a position and rotation, return the list of hexes that would be covered by the building placed that way
   */
  public function getCoveredHexes($buildingType, $pos, $rotation, $checkAvailableToBuild = true, $ignore = null)
  {
    $ignore = $ignore ?? [];
    $hexes = [];
    if ($this->player->hasPlayedCard('S219_DiversityResearcher')) {
      $ignore[WATER] = true;
      $ignore[ROCK] = true;
    }
    if ($buildingType == UNDERWATER_TUNNEL) {
      $ignore[WATER] = true;
      $ignore[UNDERWATER_TUNNEL] = true;
    }

    foreach (BUILDINGS[$buildingType] as $delta) {
      $hexOffset = self::getRotatedHex(['x' => $delta[0], 'y' => $delta[1]], $rotation);
      $hex = [
        'x' => $pos['x'] + $hexOffset['x'],
        'y' => $pos['y'] + $hexOffset['y'],
      ];

      if (!$this->isCellAvailableToBuild($hex, $ignore ?? []) && $checkAvailableToBuild) {
        return false;
      } else {
        $hexes[] = $hex;
      }
    }

    // Check constraints, if any
    $constraints = [];
    if (in_array($buildingType, ['zoo-school', \SIDE_ENTRANCE])) {
      $constraints = ['border' => 2];
    }

    foreach ($constraints as $type => $value) {
      if ($type == 'border') {
        $borders = $this->getBorderCells();
        $check = 0;
        foreach ($hexes as $hex) {
          if (in_array($hex, $borders)) {
            $check++;
          }
        }
        if ($check < $value) {
          return false;
        }
      }
    }

    return $hexes;
  }

  // Same thing for a given DB result representing a building
  public function getBuildingCoveredHexes($building, $checkAvailableToBuild = true)
  {
    return $this->getCoveredHexes($building['type'], self::extractPos($building), $building['rotation'], $checkAvailableToBuild);
  }

  /**
   * isCellAvailableToBuild: given an hex, can we build here ?
   */
  public function isCellAvailableToBuild($hex, $ignore = [])
  {
    $uid = self::getCellId($hex);
    // Can't build outside of the grid
    if (!$this->isCellValid($hex)) {
      return false;
    }
    // Can't build on an already built cell (except Conference on Australia)
    if (!is_null($this->getBuildingAtPos($hex))) {
      return $ignore['building'] ?? false;
    }
    // Can't build on water
    if (!($ignore[WATER] ?? false) && in_array($uid, $this->terrains[WATER])) {
      return false;
    }
    // Can't build on rock
    if (!($ignore[ROCK] ?? false) && in_array($uid, $this->terrains[ROCK])) {
      return false;
    }
    // Can't build on upgraded spaces
    if (!($ignore[UPGRADED_BUILD_CARD] ?? $this->player->isCardUpgraded(BUILD)) && in_array($uid, $this->upgradeNeeded)) {
      return false;
    }


    // UNDERWATER TUNNEL
    if (($ignore[UNDERWATER_TUNNEL] ?? false) && !in_array($uid, $this->terrains[WATER])) {
      return false;
    }

    return true;
  }

  /////////////////////////////
  //  _  ___           _
  // | |/ (_) ___  ___| | __
  // | ' /| |/ _ \/ __| |/ /
  // | . \| | (_) \__ \   <
  // |_|\_\_|\___/|___/_|\_\
  /////////////////////////////

  /**
   * isFarEnoughFromOtherKiosk : check whether we can build a kiosk on a given cell
   */
  protected function isFarEnoughFromOtherKiosk($hex)
  {
    foreach ($this->buildings as $building) {
      if ($building['type'] == 'kiosk' && self::getDistance($hex, self::extractPos($building)) < 3) {
        return false;
      }
    }
    return true;
  }

  /**
   * getKioskIncome : compute the income yield by the kiosks on the map
   */
  public function getKioskIncome()
  {
    $money = 0;
    foreach ($this->getBuildingsOfType(KIOSK) as $building) {
      // 1 money per neighbours
      $nbNeighbours = $this->countBuildingNeighbours($building);
      $money += $nbNeighbours;
    }

    return $money;
  }

  /**
   * countBuildingNeighbours : count the number of neighbours around a building
   *  (auxiliary function to compute kiosk income + side entrance)
   */
  public function countBuildingNeighbours($building)
  {
    $neighbours = [];
    foreach ($this->getCoveredHexes($building['type'], $building, $building['rotation'], false) as $hex) {
      foreach ($this->getNeighbours($hex) as $cell) {
        $building2 = $this->getBuildingAtPos($cell);
        // Only count each building once as a neighbourd of current building
        if (is_null($building2) || in_array($building2['id'], $neighbours) || $building2['id'] == $building['id']) {
          continue;
        }
        // Empty regular enclosure dont count
        if (in_array($building2['type'], \REGULAR_ENCLOSURES) && $building2['state'] == 0) {
          continue;
        }

        $neighbours[] = $building2['id'];
      }
    }

    return count($neighbours);
  }

  //////////////////////////////////////////////////////
  //  _____            _
  // | ____|_ __   ___| | ___  ___ _   _ _ __ ___  ___
  // |  _| | '_ \ / __| |/ _ \/ __| | | | '__/ _ \/ __|
  // | |___| | | | (__| | (_) \__ \ |_| | | |  __/\__ \
  // |_____|_| |_|\___|_|\___/|___/\__,_|_|  \___||___/
  //////////////////////////////////////////////////////

  // Given an enclosure (building), return the list of hexes around that enclosure
  public function getEnclosureNeighbourHexes($enclosure)
  {
    $cells = [];
    foreach ($this->getBuildingCoveredHexes($enclosure, false) as $cell) {
      $cells = array_merge($cells, $this->getNeighbours($cell));
    }
    return Utils::uniqueZones($cells);
  }

  // Add the number of water/rock to an enclosure
  public function addSurroundingsToEnclosure(&$enclosure)
  {
    $neighbours = $this->getEnclosureNeighbourHexes($enclosure);
    list($water, $rock) = $this->countWaterAndRock($neighbours);
    $enclosure[WATER] = $water;
    $enclosure[ROCK] = $rock;
  }

  // Return the list of enclosures with number of water/rock surrounding them
  public function getEnclosuresWithSurroundings()
  {
    $enclosures = $this->buildings->filter(function ($building) {
      return in_array($building['type'], ENCLOSURES);
    });

    foreach ($enclosures as &$enclosure) {
      $this->addSurroundingsToEnclosure($enclosure);
    }

    return $enclosures;
  }

  public function getEmptyRegularEnclosures()
  {
    return $this->buildings->filter(function ($building) {
      return in_array($building['type'], REGULAR_ENCLOSURES) && $building['state'] == 0;
    });
  }

  public function getRegularEnclosures()
  {
    return $this->buildings->filter(function ($building) {
      return in_array($building['type'], REGULAR_ENCLOSURES);
    });
  }


  /**
   * isAnimalFittingEnclosure:
   *  - $animal : object
   *  - enclosure : array
   *  - isAnimalAdded : allow to distinguish whether we try to find an empty enclosure to add an animal, or a filled enclosure to free an animal
   *  - ignoreRequirements : allow to bypass requirements check in case of release and no other option
   * => return true/false or max number of cubes
   */
  public function isAnimalFittingEnclosure($animal, $enclosure, $isAnimalAdded = true, $ignoreRequirements = false): int|bool
  {
    // Check enclosure requirements
    $requirements = $animal->getEnclosureRequirements();
    if ($this->player->hasPlayedCard('S219_DiversityResearcher')) {
      $requirements[WATER] = 0;
      $requirements[ROCK] = 0;
    }

    if (
      !$ignoreRequirements &&
      ($enclosure[WATER] < ($requirements[WATER] ?? 0) || $enclosure[ROCK] < ($requirements[ROCK] ?? 0))
    ) {
      return false;
    }

    $type = $enclosure['type'];
    $enclosureSize = $enclosure['size']; // ?? count(BUILDINGS[$type]);

    // Regular enclosure
    if (in_array($type, \REGULAR_ENCLOSURES)) {
      $size = $animal->getEnclosureSize();
      // Animal must be ok with regular enclosure (all except domestic animals & some MW)
      if ($size == 0 || $animal->isNoRegularEnclosure()) {
        return false;
      }
      // + size big enough + enclosure is free
      if ($enclosureSize < $size || $enclosure['state'] == ($isAnimalAdded ? 1 : 0)) {
        return false;
      }
      return true;
    }
    // Special enclosure
    else {
      $special = $animal->getSpecialEnclosure();
      if (empty($special['types'] ?? [])) {
        return false;
      }

      foreach ($special['types'] as $specialType) {
        // Check that this kind of special enclosure is allowed 
        if (!in_array($type, ENCLOSURE_TYPES_MAP[$specialType])) {
          continue;
        }

        // Return how many cubes can be fitted
        return $isAnimalAdded ? ($enclosureSize - $enclosure['state']) : $enclosure['state'];
      }
      return false;
    }
  }

  public function getAvailableEnclosures(
    $animal,
    $isAnimalAdded = true,
    $ignoreRequirements = false,
    $checkIsDoable = false,
    $constraint = null,
    $excludedType = null // Useful for release
  ) {
    // ENCLOSURE TYPES
    $special = $animal->getSpecialEnclosure();
    $nCubes = $special['cubes'] ?? null;
    $types = [];
    // Regular enclosures
    if ((is_null($constraint) || $constraint == REGULAR_ENCLOSURE_TYPE) && !$animal->isNoRegularEnclosure()) {
      $types[] = REGULAR_ENCLOSURE_TYPE;
    }
    // Special enclosures
    if ((is_null($constraint) || $constraint == SPECIAL_ENCLOSURE_TYPE)) {
      if (!empty($special['types'] ?? [])) {
        $types = array_merge($types, $special['types']);
      }
    }

    // LOOP ON TYPES
    $fittingEnclosuresByType = new Collection([]);
    foreach ($types as $type) {
      $enclosureTypes = ENCLOSURE_TYPES_MAP[$type];
      if (!is_null($excludedType)) {
        $enclosureTypes = array_values(array_diff($enclosureTypes, [$excludedType]));
      }
      $enclosures = $this->getEnclosuresWithSurroundings();
      $fittingEnclosures = new Collection([]);
      $totalN = 0;
      foreach ($enclosures as $enclosure) {
        if (!in_array($enclosure['type'], $enclosureTypes)) {
          continue;
        }

        // How much can we fit ?
        $n = $this->isAnimalFittingEnclosure($animal, $enclosure, $isAnimalAdded, $ignoreRequirements);
        if ($n !== false && ($n !== 0 || $nCubes === 0)) {
          if ($n !== true) {
            $n = min($n, $nCubes);
            $totalN += $n;
          }
          $fittingEnclosures[$enclosure['id']] = $n;

          // Early abort for isDoable to prevent extra computation
          if ($checkIsDoable && $type == REGULAR_ENCLOSURE_TYPE) {
            break;
          }
        }
      }

      // Do we have enough places for all the cubes ?
      if ($type != REGULAR_ENCLOSURE_TYPE && $totalN < $nCubes) {
        continue;
      }

      // Have we found enough enclosures of this type ?
      if ($fittingEnclosures->count() > 0) {
        $fittingEnclosuresByType[$type] = $fittingEnclosures;
        if ($checkIsDoable) {
          return $fittingEnclosuresByType;
        }
      }
    }

    return $fittingEnclosuresByType;
  }

  public function getReleasableEnclosures($animal, $removeSpecialEnclosure = false, $ignoredType = null)
  {
    // 1. Do you have a matching special enclosure? (animal card has the icon, enough player tokens, water/rock if needed) If so, remove the tokens.
    // 2. Otherwise, do you have a matching standard enclosure? (occupied, large enough, water/rock if needed) If so, unflip the smallest such tile.
    // 3. Otherwise, do you have a matching special enclosure, ignoring water/rock? (animal card has the icon, enough player tokens) If so, remove the tokens.
    // 4. Otherwise, do you have a matching standard enclosure, ignoring water/rock? (occupied, large enough) If so, unflip the smallest such tile.

    // First get all the available enclosure that match water/rock requirements
    $enclosuresByTypes = $this->getAvailableEnclosures($animal, false, false, false, null, $ignoredType)->filter(
      fn($enclosures, $type) =>
      !$removeSpecialEnclosure || !in_array($type, \SPECIAL_ENCLOSURES)
    );
    // If none of them, just ignore the water/rock requirements
    if ($enclosuresByTypes->empty()) {
      $enclosuresByTypes = $this->getAvailableEnclosures($animal, false, true, false, null, $ignoredType);
    }

    // Now check the special enclosure, if any
    $types = $animal->getSpecialEnclosure()['types'] ?? [];
    $filteredEnclosures = $enclosuresByTypes->filter(
      fn($enclosures, $type) =>
      // Keep only special enclosure if $removeSpecialEnclosure if false
      //  or keep all but special enclosure if $removeSpecialEnclosure is true
      in_array($type, $types) xor $removeSpecialEnclosure
    );

    if ($removeSpecialEnclosure || !$filteredEnclosures->empty()) {
      $enclosuresByTypes = $filteredEnclosures;
    }

    // Keep the smallest ones
    $regularEnclosures = $enclosuresByTypes[REGULAR_ENCLOSURE_TYPE] ?? null;
    if (!is_null($regularEnclosures)) {
      $sizes = [];
      foreach ($regularEnclosures as $enclosureId => $n) {
        $enclosure = $this->buildings[$enclosureId];
        $size = $enclosure['size']; // ?? count(BUILDINGS[$enclosure['type']]);
        $sizes[$size][$enclosure['id']] = $n;
      }

      $enclosuresByTypes[REGULAR_ENCLOSURE_TYPE] = $sizes[min(array_keys($sizes))];
    }

    return $enclosuresByTypes;
  }

  /**
   * Fill enclosure with a new animal
   */
  public function fillEnclosure($enclosureId, $animal, $n = null)
  {
    $n = $n ?? $animal->getSpecialEnclosure()['cubes'];
    $enclosure = &$this->buildings[$enclosureId];
    $newState = 1;
    if (in_array($enclosure['type'], \SPECIAL_ENCLOSURES)) {
      $newState = $enclosure['state'] + $n;
    }
    Buildings::setState($enclosureId, $newState);
    $enclosure['state'] = $newState;
    return [$enclosure, null]; // Overwritten by map9
  }

  /**
   * Free an enclosure of an animal
   */
  public function emptyEnclosure($enclosureId, $animal, $n)
  {
    $enclosure = &$this->buildings[$enclosureId];
    $newState = 0;
    if (in_array($enclosure['type'], \SPECIAL_ENCLOSURES)) {
      $newState = $enclosure['state'] - $n;
    }
    Buildings::setState($enclosureId, $newState);
    $enclosure['state'] = $newState;
    return $enclosure;
  }

  //////////////////////////////////////
  //    ____      _   _
  //   / ___| ___| |_| |_ ___ _ __ ___
  //  | |  _ / _ \ __| __/ _ \ '__/ __|
  //  | |_| |  __/ |_| ||  __/ |  \__ \
  //   \____|\___|\__|\__\___|_|  |___/
  //////////////////////////////////////

  public function getPlacementBonusHexes()
  {
    $cells = [];
    foreach ($this->bonuses as $uid => $bonus) {
      $cells[] = $this->getHexFromId($uid);
    }
    return $cells;
  }

  public function getRockHexes()
  {
    $cells = [];
    foreach ($this->terrains[ROCK] as $uid) {
      $cells[] = $this->getHexFromId($uid);
    }
    return $cells;
  }

  public function getWaterHexes()
  {
    $cells = [];
    foreach ($this->terrains[WATER] as $uid) {
      $cells[] = $this->getHexFromId($uid);
    }
    return $cells;
  }

  // Count the number of empty spaces (excluding water/rock)
  public function countEmptySpaces()
  {
    $hexes = [];
    foreach ($this->getListOfCells() as $cell) {
      if (!$this->hasTileAtPos($cell)) {
        $hexes[] = $cell;
      }
    }
    list($water, $rock) = $this->countWaterAndRock($hexes);

    return count($hexes) - $water - $rock;
  }

  // Count the number of water/rock space on a given list of hexes
  protected function countWaterAndRock($hexes)
  {
    $water = 0;
    $rock = 0;
    foreach ($hexes as $hex) {
      // If a building is over a water/rock space (due to special card), the space is no longer water/rock
      //  => EXCEPT if it's the underwater tunnel
      $building = $this->getBuildingAtPos($hex);
      if (!is_null($building) && $building['type'] != UNDERWATER_TUNNEL) {
        continue;
      }

      $uid = self::getCellId($hex);
      if (in_array($uid, $this->terrains[WATER])) {
        $water++;
      }
      if (in_array($uid, $this->terrains[ROCK])) {
        $rock++;
      }
    }
    return [$water, $rock];
  }

  /* check if water or rock hex are connected */
  public function areAllTerrainHexConnected($type)
  {
    foreach ($this->terrains[$type] as $uId) {
      $hex = self::getHexFromId($uId);
      if (!is_null($this->getBuildingAtPos($hex))) {
        continue;
      }

      $found = false;
      foreach ($this->getNeighbours($hex) as $cell) {
        if ($this->hasBuildingAtPos($cell)) {
          $found = true;
        }
      }
      if ($found === false) {
        return false;
      }
    }
    return true;
  }

  public function areBorderCellsCovered()
  {
    foreach ($this->getBorderCells() as $hex) {
      $uid = self::getCellId($hex);
      if (!is_null($this->getBuildingAtPos($hex))) {
        continue;
      }

      if (in_array($uid, $this->terrains[WATER])) {
        continue;
      }

      if (in_array($uid, $this->terrains[ROCK])) {
        continue;
      }

      return false;
    }
    return true;
  }

  /**
   * getNonBuildingCells: return the list of cells that are not considered as buildings cells
   */
  public function getNonBuildingCells()
  {
    $cells = [];
    foreach (array_merge($this->terrains[WATER], $this->terrains[ROCK]) as $uid) {
      $cells[] = self::getHexFromId($uid);
    }
    return $cells;
  }

  /**
   * isBuildingCell: return true if the cell is considered as building cells
   */
  public function isBuildingCell($cell)
  {
    $uid = self::getCellId($cell);
    return !in_array($uid, $this->terrains[WATER]) && !in_array($uid, $this->terrains[ROCK]);
  }

  /**
   * getConnectedCells: return list of cells adjacent to at least one building
   *  => useful for some sponsors
   */
  public function getConnectedCells($withoutBuildings = true)
  {
    $cells = $this->getBuildingsNeighbourCells();
    if ($withoutBuildings) {
      Utils::filter($cells, function ($cell) {
        return !$this->hasBuildingAtPos($cell);
      });
    }
    return $cells;
  }

  /**
   * getIsolatedCells: return list of cells not adjacent to any building
   *  => useful for some sponsors
   */
  public function getIsolatedCells()
  {
    return Utils::diffZones(self::getListOfCells(), $this->getBuildingsNeighbourCells());
  }

  /////////////////////////////////////////////
  //   ____      _     _   _   _ _   _ _
  //  / ___|_ __(_) __| | | | | | |_(_) |___
  // | |  _| '__| |/ _` | | | | | __| | / __|
  // | |_| | |  | | (_| | | |_| | |_| | \__ \
  //  \____|_|  |_|\__,_|  \___/ \__|_|_|___/
  ////////////////////////////////////////////

  public static function getCellId($hex)
  {
    return $hex['x'] . '_' . $hex['y'];
  }

  public static function getHexFromId($uid)
  {
    $coord = explode('_', $uid);
    return ['x' => $coord[0], 'y' => $coord[1]];
  }

  public static function extractPos($building)
  {
    return [
      'x' => $building['x'],
      'y' => $building['y'],
    ];
  }

  public static function createGrid($defaultValue = null)
  {
    $dim = ['x' => 7, 'y' => 4];
    $g = [];
    for ($x = 0; $x < $dim['x']; $x++) {
      $size = $dim['y'] - ($x % 2 == 0 ? 1 : 0);
      for ($y = 0; $y < $size; $y++) {
        $row = 2 * $y + ($x % 2 == 0 ? 1 : 0);
        $g[$x][$row] = $defaultValue;
      }
    }
    return $g;
  }

  public static function getListOfCells()
  {
    $grid = self::createGrid(0);
    $cells = [];
    foreach ($grid as $x => $col) {
      foreach ($col as $y => $t) {
        $cells[] = ['x' => $x, 'y' => $y];
      }
    }
    return $cells;
  }

  protected $_borderCells = null;
  public function getBorderCells()
  {
    if (!isset($this->_borderCells)) {
      $grid = self::createGrid(0);
      $cells = [];
      foreach ($grid as $x => $col) {
        foreach ($col as $y => $t) {
          if ($y <= 1 || $x <= 0 || $y >= 11 || $x >= 8) {
            $cells[] = ['x' => $x, 'y' => $y];
          }
        }
      }
      $this->_borderCells = $cells;
    }

    return $this->_borderCells;
  }

  protected function isCellValid($cell)
  {
    return isset($this->grid[$cell['x']][$cell['y']]);
  }

  protected function areSameCell($cell1, $cell2)
  {
    return $cell1['x'] == $cell2['x'] && $cell1['y'] == $cell2['y'];
  }

  public function getNeighbours($cell)
  {
    $cells = [];
    foreach (self::DIRECTIONS as $dirName => $dir) {
      $newCell = [
        'x' => $cell['x'] + $dir['x'],
        'y' => $cell['y'] + $dir['y'],
      ];
      if ($this->isCellValid($newCell)) {
        $cells[] = $newCell;
      }
    }
    return $cells;
  }

  protected function isIntersectionNonEmpty($cells1, $cells2)
  {
    foreach ($cells1 as $cell1) {
      foreach ($cells2 as $cell2) {
        if (self::areSameCell($cell1, $cell2)) {
          return true;
        }
      }
    }
    return false;
  }

  protected function getRotatedHex($hex, $rotation)
  {
    if ($rotation == 0 || ($hex['x'] == 0 && $hex['y'] == 0)) {
      return $hex;
    }

    $q = $hex['x'];
    $r = ($hex['y'] - $hex['x']) / 2;
    $cube = [$q, $r, -$q - $r];
    for ($i = 0; $i < $rotation; $i++) {
      $cube = [-$cube[1], -$cube[2], -$cube[0]];
    }
    return [
      'x' => $cube[0],
      'y' => 2 * $cube[1] + $cube[0],
    ];
  }

  protected function getDistance($hex1, $hex2)
  {
    $deltaX = abs($hex1['x'] - $hex2['x']);
    $deltaY = abs($hex1['y'] - $hex2['y']);
    return $deltaX + max(0, ($deltaY - $deltaX) / 2);
  }
}
