<?php

namespace Bga\Games\sanctuary\Models;

use Bga\Games\sanctuary\Managers\Players;
use Bga\Games\sanctuary\Managers\Globals;
use Bga\Games\sanctuary\Managers\Meeples;
use Bga\Games\sanctuary\Game;
use Bga\Games\Sanctuary\Tiles\OpenArea;
/*
 * ZooCard
 */

class Tile extends  \Bga\Games\sanctuary\Framework\Db\DB_Model
{
  protected $implemented = true; // For DEV only

  protected ?string $table = 'tiles';
  protected ?string $primary = 'tiles_id';
  protected array $attributes = [
    'id' => ['tiles_id', 'str'],
    'location' => 'tiles_location',
    'state' => ['tiles_state', 'int'],
    'pId' => ['player_id', 'int'],
    'extraDatas' => ['extra_datas', 'obj'],
    'x' => ['x', 'int'],
    'y' => ['y', 'int'],
  ];
  protected $id;
  protected $location;
  protected $state;
  protected $pId;
  protected $extraDatas;
  protected $x;
  protected $y;
  protected ?string $listeningIcon = null;
  protected string $listeningMode = self::MY_ZOO;
  protected ?array $listeningBonuses = null;

  protected array $staticAttributes = [
    ['supported', 'obj'],
    ['prerequisites', 'obj'],
    ['continents', 'obj'],

  ];
  protected array $prerequisite;
  protected array $continents;
  protected int $strength = 1;

  public function getIcons()
  {
    return [];
  }

  public function isSupported($players, $options)
  {
    if (Game::get()->getBgaEnvironment() == 'studio') {
      return true;
    }

    return $this->implemented;
  }

  public function getTypeStr()
  {
    return '';
  }

  public function getUiData()
  {
    return $this->jsonSerialize(); // Static datas are already in js file
  }

  public function isPlayed()
  {
    return $this->location == 'inPlay';
  }

  public function getPoolNumber()
  {
    $t = explode('-', $this->location);
    return $t[0] == 'pool' ? ((int) $t[1]) : null;
  }

  public function isInPool()
  {
    return !is_null($this->getPoolNumber());
  }

  public function getMark()
  {
    return $this->getTokensOnIt()->first();
  }

  public function isMarked(): bool
  {
    return false;
  }

  public function isOpenArea(): bool
  {
    if ($this instanceof OpenArea) {
      return true;
    }
    return false;
  }

  public function getPlayer($checkPlayed = false)
  {
    if (!$this->isPlayed() && $checkPlayed) {
      throw new \feException("Trying to get the player for a non-played card : {$this->id}");
    }

    return Players::get($this->pId);
  }

  public function getFolder()
  {
    return $this->getLocation() == 'hand' ? 0 : $this->getPoolNumber();
  }

  public function getBuyCost($player)
  {
    $cost = $this->getCost() + $this->getFolder();
    foreach ($this->getContinents() as $continent) {
      $partnerZoo = $player->getPartnerZoos($continent);
      $cost -= 3 * $partnerZoo->count();
    }
    return $cost;
  }


  // /**
  //  * Scores functions
  //  */

  // public function score()
  // {
  //   $appeal = $this->getAppealScore();
  //   if ($appeal != 0) {
  //     $this->getPlayer()->incAppeal($appeal);
  //   }

   
  // }

  /**
   * Appeal points brought by this tile at the end of the game.
   * The `appeal` attribute is either a plain number, or a string of the form
   * '<n> per [connected|adjacent] <target>'.
   */
  public function getAppealScore(): int
  {
    if (!property_exists($this, 'appeal')) {
      return 0;
    }

    $appeal = $this->getAppeal();
    if (is_int($appeal) || is_numeric($appeal)) {
      return (int) $appeal;
    }
    if (!is_string($appeal)) {
      return 0;
    }
    $appeal = trim($appeal);

    if (preg_match('/^([\d\/]+)\s+for\s+connected\s+group\s+of\s+(.+)$/i', $appeal, $match)) {
      return $this->getConnectedGroupScore(
        array_map('intval', explode('/', $match[1])),
        self::appealTargetToIcon(trim($match[2]))
      );
    }

    if (!preg_match('/^(\d+)\s+per\s+(connected\s+|adjacent\s+)?(.+)$/i', $appeal, $match)) {
      return 0;
    }

    $multiplier = (int) $match[1];
    $modifier = strtolower(trim($match[2]));
    $target = trim($match[3]);

    $count = match ($modifier) {
      'connected' => $this->countConnectedAppealTarget($target),
      'adjacent' => $this->countAdjacentAppealTarget($target),
      default => $this->countAppealTarget($target),
    };

    return $multiplier * $count;
  }

  /**
   * Size of the group of tiles carrying the target icon this tile belongs to
   */
  protected function countConnectedAppealTarget(string $target): int
  {
    $map = $this->getPlayer()->map();
    return is_null($map) ? 0 : $map->countConnectedTilesWithIcon($this, self::appealTargetToIcon($target));
  }

  /**
   * Score awarded once for the whole connected group of tiles carrying $icon, based on its size.
   * Only the first tile of the group (lowest cell id) reports it, so the group is not counted several times.
   */
  protected function getConnectedGroupScore(array $scoreBySize, string $icon): int
  {
    $map = $this->getPlayer()->map();
    if (is_null($map)) {
      return 0;
    }

    $group = $map->getConnectedGroupOf($this, $icon);
    if (empty($group)) {
      return 0;
    }

    $uids = array_keys($group);
    sort($uids, SORT_STRING);
    if ($uids[0] !== ZooMap::getCellId(['x' => $this->getX(), 'y' => $this->getY()])) {
      return 0;
    }

    return $scoreBySize[min(count($group), count($scoreBySize)) - 1];
  }

  protected function countAdjacentAppealTarget(string $target): int
  {
    $map = $this->getPlayer()->map();
    return is_null($map) ? 0 : $map->countAdjacentIcons($this, self::appealTargetToIcon($target));
  }

  protected function countAppealTarget(string $target): int
  {
    $player = $this->getPlayer();
    switch (strtolower($target)) {
      case 'tile in hand':
        return $player->getHand()->count();
      case 'open area':
        return $player->getPlayedCards(self::TILE_OPEN_AREA)->count();
      case 'project':
        return $player->getPlayedCards(self::TILE_PROJECT)->count();
      case 'building':
        return $player->getPlayedCards(self::TILE_BUILDING)->count();
      case 'different adjacent icon':
        $map = $player->map();
        return is_null($map) ? 0 : $map->countDifferentAdjacentIcons($this);
      default:
        return $player->countCardIcon(self::appealTargetToIcon($target));
    }
  }

  protected static function appealTargetToIcon(string $target): string
  {
    return ucfirst($target);
  }

  public function getScoreBonus()
  {
    return null;
  }

  /**
   * Event modifiers template
   **/
  public function isListeningTo($event)
  {
    return false;
  }

  // public function getTokensOnIt()
  // {
  //   return Meeples::getTokensOnCard($this->pId, $this->id);
  // }

  public function getIconsReaction($icons, $isOwnZoo)
  {
    // Must be listening to one icon
    if (is_null($this->listeningIcon)) {
      return [];
    }
    // If listening only to icons in my zoo, make sure it was added in my zoo
    if (!$isOwnZoo && $this->listeningMode == MY_ZOO) {
      return [];
    }
    // How many icons of that type ?
    $n = $icons[$this->listeningIcon] ?? 0;
    if ($n == 0) {
      return [];
    }

    // Now multiply the effect of each bonus by that multiplier
    $bonuses = [];
    foreach ($this->listeningBonuses as $bonus) {
      $bonus['pId'] = $this->pId;
      $type = array_keys($bonus)[0];
      $bonus[$type] *= $n;
      $bonuses[] = $bonus;
    }

    return $bonuses;
  }

  /*
   ██████╗ ██████╗ ███╗   ██╗███████╗████████╗ █████╗ ███╗   ██╗████████╗███████╗
  ██╔════╝██╔═══██╗████╗  ██║██╔════╝╚══██╔══╝██╔══██╗████╗  ██║╚══██╔══╝██╔════╝
  ██║     ██║   ██║██╔██╗ ██║███████╗   ██║   ███████║██╔██╗ ██║   ██║   ███████╗
  ██║     ██║   ██║██║╚██╗██║╚════██║   ██║   ██╔══██║██║╚██╗██║   ██║   ╚════██║
  ╚██████╗╚██████╔╝██║ ╚████║███████║   ██║   ██║  ██║██║ ╚████║   ██║   ███████║
   ╚═════╝ ╚═════╝ ╚═╝  ╚═══╝╚══════╝   ╚═╝   ╚═╝  ╚═╝╚═╝  ╚═══╝   ╚═╝   ╚══════╝

  */

  const TILE_ANIMAL = 'animal';
  const TILE_BUILDING = 'building';
  const TILE_PROJECT = 'project';
  const TILE_OPEN_AREA = 'openArea';
  const TILE_STARTING_POSITION = 'startingPosition';
  const MY_ZOO = 'my-zoo';
  const ALL_ZOO = 'all-zoo';
}
