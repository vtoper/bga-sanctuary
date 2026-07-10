<?php

namespace Bga\Games\sanctuary\Framework\Algorithms;

class PriorityQueue
{
    private $values = [];

    public function enqueue($value, $priority)
    {
        $newNode = new Node($value, $priority);
        $this->values[] = $newNode;
        usort($this->values, function ($a, $b) {
            return $a->priority - $b->priority;
        });
        return $this;
    }

    public function dequeue()
    {
        return array_shift($this->values)->value ?? null;
    }

    public function peek()
    {
        return $this->values[0]->value ?? null;
    }

    public function size()
    {
        return count($this->values);
    }

    public function isEmpty()
    {
        return count($this->values) === 0;
    }
}
