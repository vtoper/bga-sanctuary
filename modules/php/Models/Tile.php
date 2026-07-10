<?php

namespace Bga\Games\sanctuary\Models;

use Bga\Games\sanctuary\Managers\Players;
use Bga\Games\sanctuary\Managers\Globals;
use Bga\Games\sanctuary\Managers\Meeples;
use Bga\Games\sanctuary\Game;
/*
 * ZooCard
 */

class Tile extends  \Bga\Games\sanctuary\Framework\Db\DB_Model
{
  protected $implemented = true; // For DEV only

  protected string $table = 'tiles';
  protected string $primary = 'tiles_id';
  protected array $attributes = [
    'id' => ['tiles_id', 'str'],
    'location' => 'tiles_location',
    'state' => ['tiles_state', 'int'],
    'pId' => ['player_id', 'int'],
    'extraDatas' => ['extra_datas', 'obj'],
    'x' => ['x', 'int'],
    'y' => ['y', 'int']
  ];
  protected $id;
  protected $location;
  protected $state;
  protected $pId;
  protected $extraDatas;
  protected $x;
  protected $y;

  protected array $staticAttributes = [
    ['supported', 'obj'],
    ['prerequisites', 'obj'],
    ['continents', 'obj'],
  ];
  protected array $prerequisite;
  protected array $continents;

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

  public function checkConditions($player, $icons, $nCanIgnore = 0)
  {
    $conditions = [];
    $ignored = 0;
    foreach ($this->getPrerequisites() as $prerequisite => $amount) {
      $satisfied = true;
      $ignoredToAdd = 1;
      switch ($prerequisite) {
        // case PARTNER_ZOO:
        //   $continent = $this->getContinents()[0] ?? null;
        //   if (is_null($continent)) {
        //     $nZoos = $player->countPartnerZoo();
        //     $satisfied = $nZoos >= $amount;
        //     $ignoredToAdd = $amount - $nZoos;
        //   } else {
        //     $satisfied = $player->hasPartnerZoo($continent);
        //   }
        //   break;

        // case UPGRADED_ANIMALS_CARD:
        //   $satisfied = $player->isCardUpgraded(ANIMALS);
        //   break;

        // case UPGRADED_SPONSORS_CARD:
        //   $satisfied = $player->isCardUpgraded(SPONSORS);
        //   break;

        // case UPGRADED_CARDS_CARD:
        //   $satisfied = $player->isCardUpgraded(CARDS);
        //   break;

        // case MAX_25_APPEAL:
        //   $satisfied = $player->getAppeal() <= 25 + Globals::getSoloAppeal();
        //   break;

        // case KIOSK:
        //   $satisfied = $player->map()->hasBuilding(KIOSK);
        //   break;

        // case REPUTATION:
        //   $satisfied = $player->getReputation() >= $amount;
        //   break;
        // case UNIVERSITY:
        //   $satisfied = $player->countUniversities() > 0;
        //   break;
        default:
          $satisfied = $icons[$prerequisite] >= $amount;
          $ignoredToAdd = $amount - $icons[$prerequisite];
      }

      $conditions[$prerequisite] = $satisfied;
      if (!$satisfied) {
        $ignored += $ignoredToAdd;
      }
    }

    $conditions['ignored'] = $ignored;
    $conditions['valid'] = $ignored <= $nCanIgnore;
    return $conditions;
  }

  public function canBePlayed($player, $icons, $nCanIgnore = 0)
  {
    $status = $this->checkConditions($player, $icons, $nCanIgnore);
    return $status['valid'];
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
}
