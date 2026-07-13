<?php

namespace Bga\Games\sanctuary\Managers;

use Bga\Games\sanctuary\Framework\Db\CachedPieces;
use Bga\Games\sanctuary\Framework\Db\Collection;
use Bga\Games\sanctuary\Game;
use Bga\Games\sanctuary\Models\Assignment;
use Bga\Games\sanctuary\Models\ActionCard;


/* Class to manage all the action cards for Ark Nova */

class ActionCards extends CachedPieces
{
  protected static string $table = 'actioncards';
  protected static string $prefix = 'card_';
  protected static array $customFields = ['level', 'player_id', 'extra_datas', 'type'];
  protected static bool $autoIncrement = true;
  protected static bool $autoremovePrefix = false;
  protected static ?Collection $datas = null;

  protected static function cast(array|null $card): ActionCard
  {
    return self::getInstance($card['type'], $card);
  }

  protected static function getInstance(string $type, array $row = null): ActionCard
  {
    $className = "Bga\Games\sanctuary\ActionCards\Action" . $type;
    return new $className($row);
  }
  public static function getInstances(array $types)
  {
    $cards = new Collection();
    foreach ($types as $type) {
      $cards[] = self::getInstance($type);
    }
    return $cards;
  }

  /* Creation of the cards */
  protected static $actionCards = ['Project', 'Rock', 'Water', 'Forest'];
  public static function setupPlayer(int $pId, array $marineCards = [])
  {
    $cards = [];
    $startCards = ['Rock', 'Water', 'Forest'];
    $turnOrder = Players::getTurnOrder();

    $position = array_search($pId, $turnOrder);
    $cards[] = [
      'type' => 'Project',
      'player_id' => $pId,
      'location' => min($position + 1, 4), // on a 5 players game both put it at last position
      'state' => 0,
      'level' => 1,
    ];
    // first player have project on first position, other in incremental
    shuffle($startCards);
    $i = 1;
    foreach ($startCards as $type) {
      if ($i == ($position + 1)) {
        $i++;;
      }
      $cards[] = [
        'type' => $type,
        'player_id' => $pId,
        'location' => $i,
        'state' => 0,
        'level' => 1,
      ];
      $i++;
    }

    return self::create($cards, null);
  }

  public static function setupNextGame()
  {
    self::DB()
      ->delete()
      ->run();
    self::invalidate();
  }

  public static function getOfPlayer(int $pId): Collection
  {
    return self::getAll()->filter(function ($card) use ($pId) {
      return $card->getPId() == $pId;
    });
  }

  public static function getInPosition(int $pId, int $position): ActionCard
  {
    return self::getOfPlayer($pId)
      ->filter(function ($card) use ($position) {
        return $position == $card->getStrength();
      })
      ->first();
  }
}
