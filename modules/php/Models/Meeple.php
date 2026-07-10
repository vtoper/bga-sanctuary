<?php

namespace Bga\Games\sanctuary\Models;

use Bga\Games\sanctuary\Managers\Players;
use Bga\Games\sanctuary\Managers\Globals;
use Bga\Games\sanctuary\Managers\Meeples;
/*
 * Meeple
 */

class Meeple extends \Bga\Games\sanctuary\Framework\Db\DB_Model
{
  protected ?string $table = 'meeples';
  protected ?string $primary = 'meeple_id';
  protected array $attributes = [
    'id' => ['meeple_id', 'int'],
    'location' => 'meeple_location',
    'state' => 'meeple_state',
    'type' => 'type',
    'pId' => 'player_id',
  ];

  public function getLocationArg()
  {
    return explode('-', $this->getLocation())[1];
  }
}
