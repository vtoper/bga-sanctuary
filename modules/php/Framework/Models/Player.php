<?php

namespace Bga\Games\sanctuary\Framework\Models;

use Bga\Games\sanctuary\Framework\Managers\Players;

/**
 * Class representing a Player
 *
 * @property int $id Player ID
 * @property int $no Player number
 * @property string $name Player name
 * @property string $color Player color
 * @property bool $eliminated Whether the player is eliminated
 * @property int $score Player score
 * @property int $scoreAux Auxiliary player score
 * @property bool $zombie Whether the player is a zombie
 */
class Player extends \Bga\Games\sanctuary\Framework\Db\DB_Model
{
    protected ?string $table = 'player';
    protected ?string $primary = 'player_id';

    protected array $defaultAttributes = [
        'id' => ['player_id', 'int'],
        'no' => ['player_no', 'int'],
        'name' => 'player_name',
        'color' => 'player_color',
        'eliminated' => 'player_eliminated',
        'score' => ['player_score', 'int'],
        'scoreAux' => ['player_score_aux', 'int'],
        'zombie' => 'player_zombie',
    ];

    protected array $customAttributes = [];

    public function __construct($row)
    {
        $this->attributes = array_merge($this->defaultAttributes, $this->customAttributes);
        parent::__construct($row);
    }

    public function getUiData($currentPlayerId = null)
    {
        $data = parent::getUiData();

        return $data;
    }

    // public function getPref($prefId)
    // {
    //     return Preferences::get($this->id, $prefId);
    // }

    // public function getStat($name)
    // {
    //     $name = 'get' . \ucfirst($name);
    //     return Stats::$name($this->id);
    // }

    public function isCurrentPlayer()
    {
        return $this->id == Players::getCurrentId();
    }
}
