<?php

namespace Bga\Games\sanctuary\Framework\Engine;

/*
 * OrNode: a class that represent an Node with a choice (parallel)
 */

class OrNode extends AbstractNode
{
  public function __construct($info = [], $children = [])
  {
    parent::__construct($info, $children);
    $this->info['type'] = Engine::NODE_OR;
  }

  /**
   * The description of the node is the sequence of description of its children
   */
  public function getDescriptionSeparator()
  {
    return ' + ';
  }

  /**
   * An OR node is doable if at least one of its child is doable (or if the OR node itself is optional)
   */
  public function isDoable($player)
  {
    return $this->isOptional() ||
      $this->childrenReduceOr(function ($child) use ($player) {
        return $child->isDoable($player);
      });
  }

  /**
   * An OR node become optional as soon as one child is resolved
   */
  public function isOptional()
  {
    return parent::isOptional() ||
      $this->childrenReduceOr(function ($child) {
        return $child->isResolved() && $child->getResolutionArgs() != Engine::PASS;
      });
  }

  /**
   * If at least one child was resolved already, other become optional
   */
  public function areChildrenOptional()
  {
    return $this->childrenReduceOr(function ($child) {
      return $child->isResolved() && $child->getResolutionArgs() != Engine::PASS;
    });
  }

  /**
   * An OR node is resolved either when marked as resolved, either when all children are resolved already
   */
  public function isResolved()
  {
    return parent::isResolved() ||
      $this->childrenReduceAnd(function ($child) {
        return $child->isResolved();
      });
  }
}
