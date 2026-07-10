<?php

declare(strict_types=1);

namespace Bga\Games\sanctuary\Framework\Engine\Constants;

class States
{
    //ENGINE STATES
    const ST_GENERIC_GAME_STATE = 907;
    const ST_CONFIRM_TURN = 903;
    const ST_CONFIRM_PARTIAL_TURN = 904;
    const ST_RESOLVE_CHOICE = 901;
    const ST_MESSAGE = 900;
    const ST_DUMMY_END = 999;
    const ST_END_GAME = 99;
}
