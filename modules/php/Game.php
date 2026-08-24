<?php

/**
 *------
 * BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
 * Sanctuary implementation : © <Your name here> <Your email address here>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * Game.php
 *
 * This is the main file for your game logic.
 *
 * In this PHP file, you are going to defines the rules of the game.
 */

declare(strict_types=1);

namespace Bga\Games\Sanctuary;

use Bga\Games\Sanctuary\Managers\Globals;
use Bga\Games\Sanctuary\Framework\Db\Log;
use Bga\Games\Sanctuary\Framework\Db\WithGame;
use Bga\Games\Sanctuary\Framework\Engine\Engine;
use Bga\Games\Sanctuary\Framework\TurnOrderManager;
use Bga\Games\Sanctuary\Managers\ActionCards;
use Bga\Games\Sanctuary\Managers\Players;
use Bga\Games\Sanctuary\Managers\Meeples;
use Bga\Games\Sanctuary\States\Flow\SetupTurn;
use Bga\Games\Sanctuary\Managers\Tiles;
use Bga\GameFramework\States\PossibleAction;
use Bga\GameFramework\Actions\CheckAction;
use Bga\Games\Sanctuary\Framework\Managers\Config;

use Bga\Games\Sanctuary\States\Actions\Animal;

class Game extends \Bga\GameFramework\Table
{
    public static $instance = null;

    public static function get(): Game
    {
        return self::$instance;
    }

    /**
     * Your global variables labels:
     *
     * Here, you can assign labels to global variables you are using for this game. You can use any number of global
     * variables with IDs between 10 and 99. If you want to store any type instead of int, use $this->globals instead.
     *
     * NOTE: afterward, you can get/set the global variables with `getGameStateValue`, `setGameStateInitialValue` or
     * `setGameStateValue` functions.
     */
    public function __construct()
    {
        parent::__construct();
        $this->initGameStateLabels([
            'logging' => 10,
        ]);

        self::$instance = $this;
        WithGame::setGame($this);
        Engine::boot();
        Log::setResetCallback(function () {
            // Force to clear cached informations
            Globals::fetch();
            Players::invalidate();
        });


        $this->bga->notify->addDecorator(function ($message, $args) {
            if (isset($args['player'])) {
                $args['player_name'] = $args['player']->getName();
                $args['player_id'] = $args['player']->getId();
                unset($args['player']);
            }
            if (isset($args['player2'])) {
                $args['player_name2'] = $args['player2']->getName();
                $args['player_id2'] = $args['player2']->getId();
                unset($args['player2']);
            }
            if (isset($args['players'])) {
                $newArgs = [];
                $logs = [];
                foreach ($args['players'] as $i => $player) {
                    $logs[] = '${player_name' . $i . '}';
                    $newArgs['player_name' . $i] = $player->getName();
                }
                $args['players_names'] = [
                    'log' => join(', ', $logs),
                    'args' => $newArgs,
                ];
                $args['i18n'][] = 'players_names';
                unset($args['players']);
            }

            if (isset($args['player_id']) && !isset($args['player_name'])) {
                $args['player_name'] = Players::get($args['player_id'])->getName();
            }

            if (isset($args['player_id2']) && !isset($args['player_name2'])) {
                $args['player_name2'] = Players::get($args['player_id2'])->getName();
            }

            if (isset($args['cards'])) {
                $logs = [];
                foreach ($args['cards'] as $i => $card) {
                    $logs[] = '${card_name_' . $i . '}';
                    $args['i18n'][] = 'card_name_' . $i;
                    $args['card_name_' . $i] = [
                        'log' => '${card_name}',
                        'args' => [
                            'i18n' => ['card_name'],
                            'card_name' => is_array($card) ? $card['name'] : $card->getName(),
                            'card_id' => is_array($card) ? $card['id'] : $card->getId(),
                            'preserve' => ['card_id'],
                        ],
                    ];
                }
                $args['card_names'] = [
                    'log' => join(', ', $logs),
                    'args' => $args,
                ];
                $args['i18n'][] = 'card_names';
            }

            if (isset($args['actionCard'])) {
                $lvlMapping = [
                    1 => 'I',
                    2 => 'II',
                ];
                $card = $args['actionCard'];
                $args['i18n'][] = 'action_card_name';
                $args['action_card_name'] = $card->getName();
                $args['action_card_level'] = $lvlMapping[$card->getLevel()];
                $args['action_card_icon'] = '';
                $args['action_card_type'] = $card->getActionType();
                $args['preserve'][] = 'action_card_type';
            }

            return $args;
        });
    }

    ////////////////////////////////////////////////////////////
    // Exposing protected methods, please use at your own risk //
    /////////////////////////////////////////////////////////////

    // Exposing protected method getCurrentPlayerId
    public function getCurrentPId()
    {
        return $this->getCurrentPlayerId();
    }

    /**
     * Compute and return the current game progression.
     *
     * The number returned must be an integer between 0 and 100.
     *
     * This method is called each time we are in a game state with the "updateGameProgression" property set to true.
     *
     * @return int
     */
    public function getGameProgression()
    {
        // TODO: compute and return the game progression

        return 0;
    }

    /**
     * Migrate database.
     *
     * You don't have to care about this until your game has been published on BGA. Once your game is on BGA, this
     * method is called everytime the system detects a game running with your old database scheme. In this case, if you
     * change your database scheme, you just have to apply the needed changes in order to update the game database and
     * allow the game to continue to run with your new version.
     *
     * @param int $from_version
     * @return void
     */
    public function upgradeTableDb($from_version)
    {
        //       if ($from_version <= 1404301345)
        //       {
        //            // ! important ! Use `DBPREFIX_<table_name>` for all tables
        //
        //            $sql = "ALTER TABLE `DBPREFIX_xxxxxxx` ....";
        //            $this->applyDbUpgradeToAllDB( $sql );
        //       }
        //
        //       if ($from_version <= 1405061421)
        //       {
        //            // ! important ! Use `DBPREFIX_<table_name>` for all tables
        //
        //            $sql = "CREATE TABLE `DBPREFIX_xxxxxxx` ....";
        //            $this->applyDbUpgradeToAllDB( $sql );
        //       }
    }

    /*
     * Gather all information about current game situation (visible by the current player).
     *
     * The method is called each time the game interface is displayed to a player, i.e.:
     *
     * - when the game starts
     * - when a player refreshes the game page (F5)
     */
    public function getAllDatas($playerId = null): array
    {
        if ($playerId === null) {
            $playerId = Players::getCurrentId();
        }

        return [
            'players' => Players::getUiData($playerId),
            'globals' => Globals::getUiData($playerId),
            'tiles' => Tiles::getUiData(),
        ];
    }

    /**
     * This method is called only once, when a new game is launched. In this method, you must setup the game
     *  according to the game rules, so that the game is ready to be played.
     */
    protected function setupNewGame($players, $options = [])
    {
        Globals::setupNewGame($players, $options);
        Players::setupNewGame($players, $options);
        Tiles::setupNewGame($players, $options);
        foreach ($players as $pId => $player) {
            ActionCards::setupPlayer($pId);
            Meeples::setupPlayer($pId);
        }

        Log::enable();
        $this->activeNextPlayer();

        return TurnOrderManager::launchDefault("turn", SetupTurn::class, SetupTurn::class, false);
    }

    /**
     * Example of debug function.
     * Here, jump to a state you want to test (by default, jump to next player state)
     * You can trigger it on Studio using the Debug button on the right of the top bar.
     */
    public function debug_goToState(int $state = 3)
    {
        $this->gamestate->jumpToState($state);
    }

    /**
     * Another example of debug function, to easily test the zombie code.
     */
    public function debug_playOneMove()
    {
        $this->bga->debug->playUntil(fn(int $count) => $count == 1);
    }

    public function debug_test()
    {
        Players::getCurrent()->map()->getAvailableLocations();
    }

    /*
    Another example of debug function, to easily create situations you want to test.
    Here, put a card you want to test in your hand (assuming you use the Deck component).

    public function debug_setCardInHand(int $cardType, int $playerId) {
        $card = array_values($this->cards->getCardsOfType($cardType))[0];
        $this->cards->moveCard($card['id'], 'hand', $playerId);
    }
    */

    public function debug_pool()
    {
        Tiles::fillPool();
    }

    public function debug_vt()
    {
        $titi = new \Bga\Games\Sanctuary\States\Actions\TakeTile($this, Engine::getNextUnresolved());
        $titi->actTakeTile(['B116_AdventurePlayground_N']);
    }

    #[PossibleAction]
    #[CheckAction(false)]
    public function actShowEngine(bool $previous = false)
    {
        if (!$previous) {
            $this->notify->all('showEngine', '', [
                'engine' => Config::getEngine()
            ]);
        } else {
            $this->notify->all('showEngine', '', [
                'engine' => Config::getLastEngine()
            ]);
        }
    }
}
