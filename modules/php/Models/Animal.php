<?php

namespace Bga\Games\sanctuary\Models;

use Bga\Games\sanctuary\Managers\Globals;

class Animal extends Tile
{
  protected string $type = self::TILE_ANIMAL;
  protected array $staticAttributes = [
    ['supported', 'obj'],
    'type',
    'name',
    ['number', 'int'],
    ['appeal', 'int'],
    ['openArea', 'obj'],
    ['prerequisites', 'obj'],
    ['continents', 'obj'],
    ['ability', 'obj'],
    ['soloAbility', 'obj'],
  ];
  protected string $name;
  protected int $number;
  protected int $appeal;
  protected array $prerequisites;
  protected array $ability;


  // public function getBonuses()
  // {
  //   return [
  //     'appeal' => $this->getAppeal(),
  //     'conservation' => $this->getConservation(),
  //     'reputation' => $this->getReputation(),
  //   ];
  // }

  public function getIcons()
  {
    return array_merge(
      array_count_values($this->getCategories()),
      array_count_values($this->getContinents()),
      $this->getEnclosureRequirements()
    );
  }

  // public function getBuyCost($player)
  // {
  //   $cost = parent::getBuyCost($player);
  //   if ($player->hasPlayedCard('S229_ExpertInSmallAnimals') && $this->isSmall()) {
  //     $cost -= 3;
  //   }
  //   if ($player->hasPlayedCard('S230_ExpertInLargeAnimals') && $this->isLarge()) {
  //     $cost -= 4;
  //   }

  //   return max($cost, 0);
  // }

  public function checkConditions($player, $icons, $nCanIgnore = 0)
  {
    if ($player->hasPlayedCard('S263_WazaLargeAnimalProgram') && $this->isLarge()) {
      $nCanIgnore++;
    }
    if ($player->canUseMap(6)) {
      $nCanIgnore++;
    }
    // MW : Camouflage
    $nCanIgnore += Globals::getEffectCamouflage();
    // MW : bonus tile
    if ($player->hasKeptBonusTile(BONUS_IGNORE_CONDITION)) {
      $nCanIgnore += 3;
    }

    return parent::checkConditions($player, $icons, $nCanIgnore);
  }

  public function getContinent()
  {
    return $this->getContinents()[0] ?? null;
  }

  // public function isSmall()
  // {
  //   return $this->getEnclosureSize() <= 2;
  // }

  // public function isLarge()
  // {
  //   return $this->getEnclosureSize() >= 4;
  // }

  public function getSoloAbility()
  {
    if (parent::getSoloAbility() == []) {
      return $this->getAbility();
    }
    return parent::getSoloAbility();
  }

  /******** POWER  *********/
  // public function getFlockSize()
  // {
  //   foreach ($this->getAbility() as $ab => $n) {
  //     if ($ab == \FLOCK_ANIMAL) {
  //       return $n;
  //     }
  //   }
  //   return false;
  // }

  // public function getInventiveTokens()
  // {
  //   return 1;
  // }


}
