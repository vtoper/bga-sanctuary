<?php

namespace Bga\Games\sanctuary\Framework;

use Bga\GameFramework\VisibleSystemException;
use Bga\Games\sanctuary\Framework\Engine\Constants\States;
use Bga\Games\sanctuary\Framework\Managers\Config;
use Bga\Games\sanctuary\Framework\Managers\Players;
use Bga\Games\sanctuary\Game;

class TurnOrderManager
{
    public static function define($name, $order, $startCallback, $endCallback, $loop = false, $args = [])
    {
        $turnOrders = Config::getTurnOrders() ?? [];
        $turnOrders[$name] = [
            'order' => $order ?? Players::getTurnOrder(),
            'index' => -1,
            'startCallback' => $startCallback,
            'args' => $args,
            'endCallback' => $endCallback,
            'loop' => $loop,
        ];
        Config::setTurnOrders($turnOrders);
    }

    public static function launch($name, $order, $startCallback, $endCallback, $loop = false, $args = [])
    {
        self::define($name, $order, $startCallback, $endCallback, $loop, $args);
        return self::proceed($name);
    }

    public static function defineDefault($name, $startCallback, $endCallback, $loop = false)
    {
        self::define($name, null, $startCallback, $endCallback, $loop);
    }

    public static function launchDefault($name, $startCallback, $endCallback, $loop = false)
    {
        self::defineDefault($name, $startCallback, $endCallback, $loop);
        return self::proceed($name);
    }

    public static function proceed($name)
    {
        $turnOrders = Config::getTurnOrders() ?? [];
        if (!isset($turnOrders[$name])) {
            throw new VisibleSystemException('Turn order not defined: ' . $name);
        }

        // Increase index and save
        $o = $turnOrders[$name];
        $i = $o['index'] + 1;
        if ($i == count($o['order']) && $o['loop']) {
            $i = 0;
        }
        $turnOrders[$name]['index'] = $i;
        Config::setTurnOrders($turnOrders);

        if ($i < count($o['order'])) {
            Game::get()->gamestate->jumpToState(States::ST_GENERIC_GAME_STATE);
            Game::get()->gamestate->changeActivePlayer($o['order'][$i]);
            return self::jumpToOrCall($o['startCallback'], $o['args']);
        } else {
            return self::end($name);
        }
    }

    public static function end($name)
    {
        $turnOrders = Config::getTurnOrders();
        if (!isset($turnOrders[$name])) {
            throw new VisibleSystemException('Turn order not defined: ' . $name);
        }

        $o = $turnOrders[$name];
        $turnOrders[$name]['index'] = count($o['order']);
        Config::setTurnOrders($turnOrders);
        $callback = $o['endCallback'];
        return self::jumpToOrCall($callback);
    }

    public static function jumpToOrCall($mixed, $args = [])
    {
        //callback can be
        // - a state id to jump to
        // - a function to call
        // - a class name of the next state

        /** @disregard P1014 because states is hidden in id_helper*/
        if (is_int($mixed) && array_key_exists($mixed, Game::get()->gamestate->states)) {
            return Game::get()->gamestate->jumpToState($mixed);
        } else if (is_callable($mixed)) {
            return call_user_func_array($mixed, $args);
        } else {
            return $mixed;
        }
    }
}
