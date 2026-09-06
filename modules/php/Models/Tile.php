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

  public function score()
  {
    $bonus = $this->getScoreBonus();
    if (!is_null($bonus)) {
      foreach ($bonus as $b => $value) {
        $method = 'inc' . ucfirst($b);
        $this->getPlayer()->$method($value, true, $this);
      }
    }
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
