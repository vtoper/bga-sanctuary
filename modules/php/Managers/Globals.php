<?php

namespace Bga\Games\sanctuary\Managers;

use Bga\Games\sanctuary\Game;
use Bga\Games\sanctuary\Models\Component;
use Bga\Games\Sanctuary\Constants\Icons;

class Globals extends \Bga\Games\sanctuary\Framework\Db\Globals
{
    protected static array $data = [];
    protected static bool $initialized = false;
    protected static array $variables = [
        'firstPlayer' => 'int',

        // Setup
        'initialMapSelection' => 'obj',
        'initialSelection' => 'obj',

        'map' => 'int',
        'engine' => 'obj',
        // conservation board
        'conservationBoard' => 'obj',

        // Game options
        'solo' => 'bool',
        'peaceful' => 'bool',
        'firstGame' => 'bool',
        'beginner' => 'bool',

        'activeActionCard' => 'obj',

        // effets
        'effectHunter' => 'obj',

        // end of game
        'endRemainingPlayers' => 'obj',
        'endFinalScoringDone' => 'bool',
        'endTriggered' => 'bool',
        'end' => 'bool',
    ];

    /*
    * Setup new game
    */
    public static function setupNewGame(array $players, array $options): void
    {
        self::setSolo(count($players) == 1);

        self::setEndRemainingPlayers([]);
        self::setEndTriggered(false);
        self::setEndFinalScoringDone(false);

        // Conservation board
        // We take 5 randomly from the 10 available
        $conservationBoard = [];
        $available = Icons::CONTINENTS_AND_TYPES;
        shuffle($available);
        $conservationBoard = array_slice($available, 0, 5);
        self::setConservationBoard($conservationBoard);
        self::setMap(Game::$instance->tableOptions->get(Globals::OPTION_MAP));
    }

    public static function getUiData(int $playerId): array
    {
        //it is possible to filter sam data here, if needed
        return array_merge(self::getAll(), []);
    }

    /*
    ██╗  ██╗███████╗██╗     ██████╗ ███████╗██████╗ ███████╗
    ██║  ██║██╔════╝██║     ██╔══██╗██╔════╝██╔══██╗██╔════╝
    ███████║█████╗  ██║     ██████╔╝█████╗  ██████╔╝███████╗
    ██╔══██║██╔══╝  ██║     ██╔═══╝ ██╔══╝  ██╔══██╗╚════██║
    ██║  ██║███████╗███████╗██║     ███████╗██║  ██║███████║
    ╚═╝  ╚═╝╚══════╝╚══════╝╚═╝     ╚══════╝╚═╝  ╚═╝╚══════╝

    */



    /*
     ██████╗ ██████╗ ███╗   ██╗███████╗████████╗ █████╗ ███╗   ██╗████████╗███████╗
    ██╔════╝██╔═══██╗████╗  ██║██╔════╝╚══██╔══╝██╔══██╗████╗  ██║╚══██╔══╝██╔════╝
    ██║     ██║   ██║██╔██╗ ██║███████╗   ██║   ███████║██╔██╗ ██║   ██║   ███████╗
    ██║     ██║   ██║██║╚██╗██║╚════██║   ██║   ██╔══██║██║╚██╗██║   ██║   ╚════██║
    ╚██████╗╚██████╔╝██║ ╚████║███████║   ██║   ██║  ██║██║ ╚████║   ██║   ███████║
     ╚═════╝ ╚═════╝ ╚═╝  ╚═══╝╚══════╝   ╚═╝   ╚═╝  ╚═╝╚═╝  ╚═══╝   ╚═╝   ╚══════╝

    */

    const OPTION_MAP = 110;
}
