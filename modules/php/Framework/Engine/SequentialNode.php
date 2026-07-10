<?php

namespace Bga\Games\sanctuary\Framework\Engine;

use Bga\GameFramework\VisibleSystemException;

/*
 * SequentialNode: a class that represent a sequence of actions
 */

class SequentialNode extends AbstractNode
{
  public function __construct($info = [], $children = [])
  {
    parent::__construct($info, $children);
    $this->info['type'] = Engine::NODE_SEQUENTIAL;
  }

  /**
   * The description of the node is the sequence of description of its children
   */
  public function getDescriptionSeparator()
  {
    return ', ';
  }

  /**
   * A SEQ node is doable if all its children are doable (or if the SEQ node itself is optional)
   * WARNING: this is a very basic check that does not cover the case where the first action might make the second one doable
   *  -> maybe it would make more sense to only check first action ?
   */
  public function isDoable($player)
  {
    return $this->childrenReduceAnd(function ($child) use ($player) {
      return $child->isDoable($player) || $child->isOptional();
    });
  }

  /**
   * An SEQ node is resolved either when marked as resolved, either when all children are resolved already
   *  => if the node was actually an action node and is not resolved fully yet => go back to him
   */
  public function isResolved()
  {
    return parent::isResolved() ||
      $this->childrenReduceAnd(function ($child) {
        return $child->isResolved();
      });
  }

  /**
   * Just return the first unresolved children, unless the node itself is optional
   */
  public function getNextUnresolved()
  {
    if ($this->isResolved()) {
      return null;
    }

    if ($this->isOptional()) {
      return $this;
    }

    foreach ($this->children as $child) {
      if (!$child->isResolved()) {
        return $child->getNextUnresolved();
      }
    }
  }

  /**
   * We only enter this function if the user decide to enter the SEQ (in the case where the node is optional)
   */
  public function choose($childIndex, $auto = false)
  {
    if ($childIndex != 0) {
      throw new VisibleSystemException('SEQ Choice shouldnt happen with $childIndex different from 0');
    }
    $this->info['optional'] = false; // Mark the node as mandatory
  }
}
