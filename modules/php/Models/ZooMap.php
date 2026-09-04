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

  public function checkMandatoryOpenAreas($mandatoryOpenAreas, $locations, $canAddOpenAreas = true): array
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

        if (!$canAddOpenAreas) {
          continue 2; // if we cannot add an open area, skip this location
        }

        if (!$this->hasTileAtPos($adjacentCell)) {
          $neededOpenAreas[$loc['x'] . '_' . $loc['y']][] = $adjacentCell;
        }
      }
      $newLocations[] = $loc;
    }
    return [$newLocations, $neededOpenAreas];
  }


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
    $icons = $this->player->countCardIcons();

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
        return count(array_intersect_key($icons, array_flip(Icons::CONTINENTS))) >= $amount;

      case Prerequisites::HAVE_DIFFERENT_ANIMAL_ICONS:
        return count(array_intersect_key($icons, array_flip(Icons::ANIMAL_TYPES))) >= $amount;

      case Prerequisites::HAVE_DIFFERENT_ANIMAL_AND_CONTINENT_ICONS:
        return count(array_intersect_key($icons, array_flip(Icons::CONTINENTS_AND_TYPES))) >= $amount;

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

  protected function getDistance($hex1, $hex2)
  {
    $deltaX = abs($hex1['x'] - $hex2['x']);
    $deltaY = abs($hex1['y'] - $hex2['y']);
    return $deltaX + max(0, ($deltaY - $deltaX) / 2);
  }
}
