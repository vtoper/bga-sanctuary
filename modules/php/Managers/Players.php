<?php

namespace Bga\Games\sanctuary\Managers;

use Bga\Games\sanctuary\Framework\Db\Collection;
use Bga\Games\sanctuary\Game;
use Bga\Games\sanctuary\Models\Player;

/*
 * Players manager : allows to easily access players ...
 *  a player is an instance of Player class
 */

class Players extends \Bga\Games\sanctuary\Framework\Managers\Players
{
    protected static ?Collection $datas = null;
    protected static function cast(array $row): Player
    {
        return new \Bga\Games\sanctuary\Models\Player($row);
    }

    public static function setupNewGame(array $players, array $options = []): void
    {
        parent::setupNewGame($players);
        // do custom setup by using self::getAll() or similar

    }

    private static function getColorName($color)
    {
        return $colorNames = [
            "4C759C" => "blue",
            "75AB33" => "green",
            "FBE606" => "yellow",
            "DE0F1B" => "red",
            "000000" => "black",
        ][$color] ?? "unknown";
    }



    public static function score($scoringFunction, $message, $limitScore = 20)
    {
        // Game::get()->bga->notify->all("message", $message, []);
        // self::getAll()->forEach(function ($player) use ($scoringFunction, $limitScore) {
        //     if (is_callable($scoringFunction)) {
        //         $score = $scoringFunction($player);
        //     } else {
        //         $score = $player->{$scoringFunction}();
        //     }

        //     if ($limitScore) {
        //         $score = min($score, $limitScore);
        //     }
        //     $player->addFame($score);
        // });
    }
}
