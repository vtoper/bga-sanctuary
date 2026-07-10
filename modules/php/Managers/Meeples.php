<?php

namespace Bga\Games\sanctuary\Managers;

use Bga\Games\sanctuary\Framework\Db\CachedPieces;
use Bga\Games\sanctuary\Framework\Db\Collection;
use Bga\Games\sanctuary\Game;
use Bga\Games\sanctuary\Models\Meeple;


class Meeples extends CachedPieces
{
  protected static string $table = 'meeples';
  protected static string $prefix = 'meeple_';
  protected static array $customFields = ['type', 'player_id'];
  protected static ?Collection $datas = null;
  protected static bool $autoremovePrefix = false;

  protected static function cast(array $meeple): Meeple
  {
    return new Meeple($meeple);
  }
  public static function getUiData()
  {
    return self::getAll()->toArray();
  }

  ////////////////////////////////////
  //  ____       _
  // / ___|  ___| |_ _   _ _ __
  // \___ \ / _ \ __| | | | '_ \
  //  ___) |  __/ |_| |_| | |_) |
  // |____/ \___|\__|\__,_| .__/
  //                      |_|
  ////////////////////////////////////

  public static function setupPlayer(int $playerId)
  {
    $meeples = [];
    foreach (self::UPGRADE_TOKENS as $type) {
      $meeples[] = [
        'type' => $type,
        'player_id' => $playerId,
        'location' => 'reserve',
        'state' => 0,
      ];
    }

    foreach (self::CONSERVATION_TOKENS as $type) {
      $meeples[] = [
        'type' => $type,
        'player_id' => $playerId,
        'location' => 'reserve',
        'state' => 0,
      ];
    }
    return self::create($meeples);
  }


  public static function countMeeples(string|array $location, string|array $type): int
  {
    return self::getOfType($location, $type)->count();
  }

  public static function getOfType(string|array $location, string|array $type): Collection
  {
    return self::getFilteredQuery(null, $location, $type)->get();
  }


  /**
   * Generic base query
   */
  public static function getFilteredQuery(?int $pId, string|array $location, string|array $type): \Bga\Games\sanctuary\Framework\Db\QueryBuilder
  {
    $query = self::getSelectQuery();

    if ($pId != null) {
      $query = $query->wherePlayer($pId);
    }
    if ($location != null) {
      $query = $query->where('meeple_location', strpos($location, '%') === false ? '=' : 'LIKE', $location);
    }
    if ($type != null) {
      if (is_array($type)) {
        $query = $query->whereIn('type', $type);
      } else {
        $query = $query->where('type', strpos($type, '%') === false ? '=' : 'LIKE', $type);
      }
    }
    $query = $query->orderBy('meeple_state', 'ASC');
    return $query;
  }

  /*
     ██████╗ ██████╗ ███╗   ██╗███████╗████████╗ █████╗ ███╗   ██╗████████╗███████╗
    ██╔════╝██╔═══██╗████╗  ██║██╔════╝╚══██╔══╝██╔══██╗████╗  ██║╚══██╔══╝██╔════╝
    ██║     ██║   ██║██╔██╗ ██║███████╗   ██║   ███████║██╔██╗ ██║   ██║   ███████╗
    ██║     ██║   ██║██║╚██╗██║╚════██║   ██║   ██╔══██║██║╚██╗██║   ██║   ╚════██║
    ╚██████╗╚██████╔╝██║ ╚████║███████║   ██║   ██║  ██║██║ ╚████║   ██║   ███████║
     ╚═════╝ ╚═════╝ ╚═╝  ╚═══╝╚══════╝   ╚═╝   ╚═╝  ╚═╝╚═╝  ╚═══╝   ╚═╝   ╚══════╝

    */
  const UPGRADE_CONSERVATION = 'upgradeConservation';
  const UPGRADE_2PROJECTS = 'upgrade2Projects';
  const UPGRADE_3CONNECTED = 'upgrade3Connected';
  const UPGRADE_4ANIMALS = 'upgrade4Animals';
  const UPGRADE_TOKENS = [
    self::UPGRADE_CONSERVATION,
    self::UPGRADE_2PROJECTS,
    self::UPGRADE_3CONNECTED,
    self::UPGRADE_4ANIMALS
  ];

  const CONSERVATION_2 = 'conservation2';
  const CONSERVATION_3 = 'conservation3';
  const CONSERVATION_4 = 'conservation4';
  const CONSERVATION_5 = 'conservation5';
  const CONSERVATION_TOKENS = [
    self::CONSERVATION_2,
    self::CONSERVATION_3,
    self::CONSERVATION_4,
    self::CONSERVATION_5
  ];
}
