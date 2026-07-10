<?php

namespace Bga\Games\sanctuary\Framework\Db;

use Bga\GameFramework\Table;

abstract class WithGame
{
    protected static ?Table $game = null;

    public static function setGame(Table $game)
    {
        self::$game = $game;
    }
}
