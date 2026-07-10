<?php

namespace Bga\Games\sanctuary\Framework\Algorithms;

class Dijkstra
{
    public static function shortestPath($startNode, $targetNode, $getNeighborsCallback, $getCostCallback)
    {
        $priorityQueue = new PriorityQueue();
        $distances = new \WeakMap();
        $previous = new \WeakMap();

        $priorityQueue->enqueue($startNode, 0);
        $distances->offsetSet($startNode, 0);
        $previous->offsetSet($startNode, null);

        while (!$priorityQueue->isEmpty()) {
            $currentNode = $priorityQueue->dequeue();

            if ($currentNode === $targetNode) {
                // Reconstruct the path from startNode to targetNode
                $path = [];
                while ($currentNode !== null) {
                    array_unshift($path, $currentNode);
                    $currentNode = $previous->offsetGet($currentNode);
                }
                return $path; // Return the shortest path as an array of nodes
            }

            $neighbors = $getNeighborsCallback($currentNode);
            foreach ($neighbors as $neighbor) {
                $cost = $getCostCallback($currentNode, $neighbor);
                $altDistance = $distances->offsetGet($currentNode) + $cost;

                if (!$distances->offsetExists($neighbor) || $altDistance < $distances->offsetGet($neighbor)) {
                    $distances->offsetSet($neighbor, $altDistance);
                    $previous->offsetSet($neighbor, $currentNode);
                    $priorityQueue->enqueue($neighbor, $altDistance);
                }
            }
        }

        return []; // Return empty array if no path found
    }

    public static function distance($startNode, $targetNode, $getNeighborsCallback, $getCostCallback)
    {
        return count(self::shortestPath($startNode, $targetNode, $getNeighborsCallback, $getCostCallback)) - 1;
    }
}
