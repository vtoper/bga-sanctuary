<?php

namespace Bga\Games\sanctuary\Models;

use Bga\Games\sanctuary\Managers\Globals;

class Project extends Tile
{
  protected string $type = self::TILE_PROJECT;
  // TODO
  protected array $staticAttributes = [
    ['supported', 'obj'],
    'type',
    'name',
    ['number', 'int'],
    ['appeal', 'int'],
    ['openArea', 'obj'],
    ['prerequisites', 'obj'],
    ['continents', 'obj'],
    ['effects', 'obj'],
    ['strength', 'int'],
    ['release', 'bool'],
    ['releaseIcon', 'str'],
    ['categories', 'obj'],
    ['gender', 'str'],
  ];
  protected string $name;
  protected int $number;
  protected int|string $appeal;
  protected array $openArea;
  protected array $prerequisites;
  protected array $continents;
  protected array $effects;
  protected ?array $listeningIcon = null;
  protected string $listeningMode = self::MY_ZOO;
  protected ?array $listeningBonuses = null;
  protected int $strength;
  protected string $gender;
  protected array $categories = [];
  protected bool $release = false;
  protected string $releaseIcon = '';



  public function countIcon($icon)
  {
    return $this->getPlayer()->countCardIcon($icon);
  }

  public function matchesPlayConstraints(int $maxStrength): bool
  {
    if ($this->getStrength() > $maxStrength) {
      return false;
    }
    return true;
  }

  public function getBonuses()
  {
    $bonuses = [];
    if ($this->getAppeal() > 0) {
      $bonuses[] = [APPEAL => $this->getAppeal()];
    }
    if ($this->getConservation() > 0) {
      $bonuses[] = [CONSERVATION => $this->getConservation()];
    }
    if ($this->getReputation() > 0) {
      $bonuses[] = [REPUTATION => $this->getReputation()];
    }
    return $bonuses;
  }

  public function getIcons()
  {
    return array_merge(
      array_count_values($this->getCategories()),
      array_count_values($this->getContinents())
    );
  }

  public function getIncome()
  {
    return null;
  }

  public function getImmediate()
  {
    return null;
  }

  public function getPassive()
  {
    return [];
  }


  public function getIconsReaction($icons, $isOwnZoo)
  {
    // Must be listening to one icon
    if (is_null($this->listeningIcon)) {
      return [];
    }
    // If listening only to icons in my zoo, make sure it was added in my zoo
    if (!$isOwnZoo && $this->listeningMode == self::MY_ZOO) {
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

      // Cant do easy multiplication for some sponsor
      if (in_array($this->id, ['S270_MarineResearchExpedition'])) {
        for ($i = 0; $i < $n; $i++) {
          $bonuses[] = $bonus;
        }
      }
      // General case : *$n
      else {
        $type = array_keys($bonus)[0];
        $bonus[$type] *= $n;
        $bonuses[] = $bonus;
      }
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

  const MY_ZOO = 'my-zoo';
  const ALL_ZOO = 'all-zoo';
}
