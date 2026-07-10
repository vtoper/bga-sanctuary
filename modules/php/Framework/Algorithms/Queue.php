<?php

namespace Bga\Games\sanctuary\Framework\Algorithms;

class Queue
{
    private $first;
    private $last;
    private $length;

    public function __construct($value = null)
    {
        if ($value === null) {
            $this->first = null;
            $this->last = null;
            $this->length = 0;
            return;
        }

        $newNode = new Node($value);
        $this->first = $newNode;
        $this->last = $newNode;
        $this->length = 1;
    }

    public function enqueue($value)
    {
        $newNode = new Node($value);
        if ($this->length === 0) {
            $this->first = $newNode;
            $this->last = $newNode;
        } else {
            $this->last->next = $newNode;
            $this->last = $newNode;
        }
        $this->length++;
        return $this;
    }

    public function dequeue()
    {
        if ($this->length === 0) {
            return null;
        }

        $temp = $this->first;
        if ($this->length === 1) {
            $this->first = null;
            $this->last = null;
        } else {
            $this->first = $this->first->next;
            $temp->next = null;
        }
        $this->length--;
        return $temp->value;
    }

    public function peek()
    {
        if ($this->first === null) {
            return null;
        }
        return $this->first->value;
    }

    public function size()
    {
        return $this->length;
    }

    public function isEmpty()
    {
        return $this->length === 0;
    }
}
