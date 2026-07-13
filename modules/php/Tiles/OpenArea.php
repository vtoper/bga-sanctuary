<?php

namespace Bga\Games\sanctuary\Tiles;

use Bga\Games\sanctuary\Managers\Globals;
use Bga\Games\sanctuary\Models\Tile;

class OpenArea extends Tile
{
    protected string $type = self::TILE_OPEN_AREA;
    protected array $staticAttributes = [
        ['supported', 'obj'],
        'type',
        'name',
        ['appeal', 'int'],
    ];
    protected string $name;
    protected int $appeal = 1;
}
