<?php

namespace Bga\Games\sanctuary\Framework\Algorithms;

use WeakMap;

class BreadthFirst
{
    public static function search($startNode, $targetNode, $getNeighborsCallback)
    {
        return count(self::shortestPath($startNode, $targetNode, $getNeighborsCallback)) > 0;
    }

    public static function distance($startNode, $targetNode, $getNeighborsCallback)
    {
        return count(self::shortestPath($startNode, $targetNode, $getNeighborsCallback)) - 1;
    }

    public static function count($startNode, $getNeighborsCallback)
    {
        $queue = new Queue();
        $visited = new WeakMap();
        $count = 0;
        $queue->enqueue($startNode);
        $visited->offsetSet($startNode, true);

        while (!$queue->isEmpty()) {
            $currentNode = $queue->dequeue();
            $count++;

            $neighbors = $getNeighborsCallback($currentNode);
            foreach ($neighbors as $neighbor) {
                if (!$visited->offsetExists($neighbor)) {
                    $visited->offsetSet($neighbor, true);
                    $queue->enqueue($neighbor);
                }
            }
        }

        return $count; // Return total count of reachable nodes
    }

    public static function shortestPath($startNode, $targetNode, $getNeighborsCallback)
    {
        $queue = new Queue();
        $visited = new WeakMap();
        $predecessor = new WeakMap();
        $queue->enqueue($startNode);
        $visited->offsetSet($startNode, true);
        $predecessor->offsetSet($startNode, null);

        while (!$queue->isEmpty()) {
            $currentNode = $queue->dequeue();

            if ($currentNode === $targetNode) {
                // Reconstruct the path from startNode to targetNode
                $path = [];
                while ($currentNode !== null) {
                    array_unshift($path, $currentNode);
                    $currentNode = $predecessor->offsetGet($currentNode);
                }
                return $path; // Return the shortest path as an array of nodes
            }

            $neighbors = $getNeighborsCallback($currentNode);
            foreach ($neighbors as $neighbor) {
                if (!$visited->offsetExists($neighbor)) {
                    $visited->offsetSet($neighbor, true);
                    $predecessor->offsetSet($neighbor, $currentNode);
                    $queue->enqueue($neighbor);
                }
            }
        }

        return []; // Target node not reachable, return empty path
    }

    public static function getAllEdges($startNode, $getNeighborsCallback)
    {
        $queue = new Queue();
        $visited = new WeakMap();
        $edges = [];
        $queue->enqueue($startNode);
        $visited->offsetSet($startNode, true);

        while (!$queue->isEmpty()) {
            $currentNode = $queue->dequeue();

            $neighbors = $getNeighborsCallback($currentNode);
            foreach ($neighbors as $neighbor) {
                if (!$visited->offsetExists($neighbor)) {
                    $visited->offsetSet($neighbor, true);
                    $edges[] = [$currentNode, $neighbor];
                    $queue->enqueue($neighbor);
                }
            }
        }

        return $edges; // Return all edges in the graph as an array of [node, neighbor] pairs
    }
}
