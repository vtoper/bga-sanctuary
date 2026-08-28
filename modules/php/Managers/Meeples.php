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

    foreach (self::ACHIEVEMENT_TOKENS as $type) {
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

  public static function getAvailableAchievementMarkers(int $playerId): Collection
  {
    return self::getFilteredQuery($playerId, 'reserve', self::ACHIEVEMENT_TOKENS)->get();
  }

  /**
   * Achievement markers already placed by the player on the conservation board
   */
  public static function getPlacedAchievementMarkers(int $playerId): Collection
  {
    return self::getFilteredQuery($playerId, self::LOCATION_CONSERVATION_BOARD . '-%', self::ACHIEVEMENT_TOKENS)->get();
  }

  /**
   * Number of icons required to place the given achievement marker
   */
  public static function getAchievementRequirement(string $type): int
  {
    return self::ACHIEVEMENT_REQUIREMENTS[$type] ?? 0;
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

  const ACHIEVEMENT_2 = 'achievement2';
  const ACHIEVEMENT_3 = 'achievement3';
  const ACHIEVEMENT_4 = 'achievement4';
  const ACHIEVEMENT_5 = 'achievement5';
  const ACHIEVEMENT_TOKENS = [
    self::ACHIEVEMENT_2,
    self::ACHIEVEMENT_3,
    self::ACHIEVEMENT_4,
    self::ACHIEVEMENT_5
  ];

  const ACHIEVEMENT_REQUIREMENTS = [
    self::ACHIEVEMENT_2 => 2,
    self::ACHIEVEMENT_3 => 3,
    self::ACHIEVEMENT_4 => 4,
    self::ACHIEVEMENT_5 => 5
  ];

  const CONSERVATION_MARKER = 'conservationMarker';

  const LOCATION_CONSERVATION_BOARD = 'conservationBoard';
}
