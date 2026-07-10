<?php

namespace Bga\Games\sanctuary\Framework\Managers;

use Bga\Games\sanctuary\Framework\Db\Globals;

class Config extends Globals
{
    protected static array $data = [];
    protected static bool $initialized = false;
    protected static array $variables = [
        'engine' => 'obj',
        'lastEngine' => 'obj',
        'turnOrders' => 'obj',
        'endEngineCallback' => 'obj',

        'engineChoices' => 'int',
        'anytimeRecursion' => 'int',
    ];
}
