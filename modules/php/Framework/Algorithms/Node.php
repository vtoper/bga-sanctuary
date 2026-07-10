<?php

namespace Bga\Games\sanctuary\Framework\Algorithms;

class Node
{
    public $value;
    public $priority;
    public $next;

    public function __construct($value, $priority = 0)
    {
        $this->value = $value;
        $this->priority = $priority;
        $this->next = null;
    }
}
