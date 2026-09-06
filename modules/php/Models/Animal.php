<?php

namespace Bga\Games\sanctuary\Models;

use Bga\Games\sanctuary\Managers\Globals;
use Bga\Games\sanctuary\Constants\Icons;

class Animal extends Tile
{
  protected string $type = self::TILE_ANIMAL;
  protected array $staticAttributes = [
    ['supported', 'obj'],
    'type',
    'name',
    ['number', 'int'],
    ['appeal', 'obj'],
    ['openAreas', 'obj'],
    ['prerequisites', 'obj'],
    ['continents', 'obj'],
    ['ability', 'obj'],
    ['soloAbility', 'obj'],
    ['pair', 'str'],
    ['gender', 'str'],
    ['strength', 'int'],
    ['categories', 'obj'],
    ['effect', 'obj'],
    ['reduction', 'obj'],
    ['listeningIcon', 'str']
  ];
  protected string $name;
  protected int $number;
  protected int|string $appeal;
  protected array $prerequisites;
  protected array $ability;
  protected ?string $pair = null;
  protected ?string $gender = null;
  protected array $categories = [];
  protected array $continents = [];
  protected array $openAreas = [];
  protected array $effect = [];
  protected array $reduction = [];

  public function getIcons()
  {
    return array_merge(
      array_count_values($this->getCategories()),
      array_count_values($this->getContinents())
    );
  }


  public function getContinent()
  {
    return $this->getContinents()[0] ?? null;
  }

  /**
   * Return the animal's habitat (one of Icons::HABITATS), or null if it has no habitat (undefined)
   */
  public function getHabitat(): ?string
  {
    foreach ($this->getCategories() as $category) {
      if (in_array($category, Icons::HABITATS)) {
        return $category;
      }
    }
    return null;
  }

  /**
   * Whether this animal can be played given the state constraints (max strength and required habitat)
   */
  public function matchesPlayConstraints(int $maxStrength, ?string $habitat, array $reduction = []): bool
  {
    $strength = $this->getStrength();
    $icons = $this->getIcons();
    foreach ($reduction as $type => $value) {
      if (isset($icons[$type])) {
        $strength -= $value;
      }
    }

    if ($strength > $maxStrength) {
      return false;
    }

    $animalHabitat = $this->getHabitat();
    return is_null($animalHabitat) || is_null($habitat) || $habitat == '' || $animalHabitat == $habitat || $animalHabitat == Icons::UNDEFINED;
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
}
