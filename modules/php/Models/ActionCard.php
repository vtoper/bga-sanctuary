<?php

namespace Bga\Games\sanctuary\Models;

use Bga\Games\sanctuary\Managers\Players;
use Bga\Games\sanctuary\Managers\Globals;
use Bga\Games\sanctuary\Managers\Meeples;
use Bga\Games\sanctuary\Managers\Actions;
use Bga\Games\sanctuary\Framework\Engine\Utils;
use Bga\Games\sanctuary\Game;

/*
 * Action Card
 */

class ActionCard extends \Bga\Games\sanctuary\Framework\Db\DB_Model
{
  protected ?string $table = 'actioncards';
  protected ?string $primary = 'card_id';

  protected array $attributes = [
    'id' => ['card_id', 'int'],
    'strength' => ['card_location', 'int'],
    'pId' => ['player_id', 'int'],
    'extraDatas' => ['extra_datas', 'obj'],
    'type' => ['type', 'str'],
    'status' => ['card_state', 'int'],
    'level' => ['level', 'int'],
  ];
  protected ?int $id;
  protected ?string $type;
  protected ?string $pId;

  protected array $staticAttributes = ['actionType', ['number', 'int'], 'name', 'descI', 'descII', 'tooltip'];
  protected string $actionType;
  protected int $number = 0;
  protected string $name;
  protected array $descI;
  protected array $descII;
  protected array $tooltip = [];

  public function getAction($ctx = null): \Bga\GameFramework\States\GameState
  {
    $stateClass = "\\Bga\\Games\\sanctuary\\States\\Actions\\" . $this->actionType;
    return new $stateClass(Game::get(), $ctx);
  }

  public function getCurrentStrength(): int
  {
    $strength = $this->getStrength();
    $player = Players::get($this->pId);
    if ($this->getLevel() == 2) {
      $strength++;
    }

    return $strength;
  }

  public function getPlayableStrengths($player, $ignoreXTokens = false): array
  {
    $baseStrength = $this->getCurrentStrength();
    $maxStrength = $ignoreXTokens ? 10 : $baseStrength;

    $strengths = [];
    for ($strength = $baseStrength; $strength <= $maxStrength; $strength++) {
      // If the card strength reduce below 1 with constriction, cannot play this strength
      if ($strength < 1) {
        continue;
      }

      if ($this->canBePlayed($player, $strength)) {
        $strengths[$strength] = $strength - $baseStrength;
      }
    }

    return $strengths;
  }

  public function canBePlayed($player, $strength = null)
  {
    // in Sanctuary, you can always choose a tile
    return true;

    $strength = $strength ?? $this->getStrength();
    return $this->getAction(['strength' => $strength, 'lvl' => $this->getLevel(), 'number' => $this->number])->isDoable($player->getId());
  }

  public function getFlow($strength = null)
  {
    $strength = $strength ?? $this->getStrength();
    return [
      'action' => $this->actionType,
      'args' => [
        'strength' => $strength,
        'lvl' => $this->getLevel(),
        'number' => $this->number,
      ],
    ];
  }

  public function getTaggedFlow($player, $strength = null)
  {
    // Add card context for listeners
    return Utils::tagTree($this->getFlow($strength), [
      'pId' => $player->getId(),
      'cardId' => $this->id,
    ]);
  }


  public function getAfterFinishingFlow($strength = null)
  {
    return [];
  }

  public function getAfterFinishingTaggedFlow($player, $strength = null)
  {
    $flow = $this->getAfterFinishingFlow($strength);
    return empty($flow) ? [] : Utils::tagTree($flow, [
      'pId' => $player->getId(),
      'cardId' => $this->id,
    ]);
  }
}
