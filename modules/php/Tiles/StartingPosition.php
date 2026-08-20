<?php

namespace Bga\Games\sanctuary\Tiles;

use Bga\Games\sanctuary\Managers\Globals;
use Bga\Games\sanctuary\Models\Tile;

class StartingPosition extends Tile
{
    protected string $type = self::TILE_STARTING_POSITION;
    protected array $staticAttributes = [
        ['supported', 'obj'],
        'type',
        'name',
        ['appeal', 'int'],
    ];
    protected string $name;
}
