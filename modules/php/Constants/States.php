<?php

declare(strict_types=1);

namespace Bga\Games\sanctuary\Constants;

class States
{
    //MAIN FLOW
    const ST_TURN_PREPARATION = 10;
    const ST_START_ASSIGNMENT = 40;
    const ST_END_TURN_PHASE = 80;
    const ST_END_GAME_SCORING = 90;

    //SETUP STATES
    const ST_SETUP_TURN = 810;


    //ACTION STATES
    const ST_PROJECT = 100;
    const ST_TAKE_TILE = 120;
    const ST_PLAY_CARD = 130;
    const ST_CHOOSE_ACTION_CARD = 140;
    const ST_BUILDING = 150;

    // Automatic state
    const ST_DRAW_TILE = 200;
    const ST_CLEANUP = 210;
    const ST_TAKE_BONUS = 220;
}
