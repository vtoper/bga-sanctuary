<?php

namespace Bga\Games\sanctuary\Framework\Engine;

use Bga\GameFramework\VisibleSystemException;

/*
 * AbstractNode: a class that represent an abstract Node
 */

class AbstractNode
{
  protected $children = [];
  protected $parent = null;
  protected $info = [];

  public function __construct($info = [], $children = [])
  {
    $this->info = $info;
    $this->children = $children;

    foreach ($this->children as $child) {
      $child->attach($this);
    }
  }

  /**********************
   *** Tree utilities ***
   **********************/
  public function attach($parent)
  {
    $this->parent = $parent;
  }

  public function replaceAtPos($node, $index)
  {
    $this->children[$index] = $node;
    $node->attach($this);
    return $node;
  }

  public function getIndex()
  {
    if ($this->parent == null) {
      return null;
    }

    foreach ($this->parent->getChildren() as $i => $child) {
      if ($child === $this) {
        return $i;
      }
    }
    throw new \feException(debug_print_backtrace());
    throw new VisibleSystemException("Can't find index of a child");
  }

  public function replace($newNode)
  {
    $index = $this->getIndex();
    if (is_null($index)) {
      throw new VisibleSystemException('Trying to replace the root');
    }
    return $this->parent->replaceAtPos($newNode, $index);
  }

  public function pushChild($child)
  {
    array_push($this->children, $child);
    $child->attach($this);
  }

  public function insertAsBrother($newNode)
  {
    $index = $this->getIndex();
    if (is_null($index)) {
      throw new VisibleSystemException('Trying to insert a brother of the root');
    }
    // Ensure parent is a seq node
    if (!$this->parent instanceof SequentialNode) {
      $newParent = new SequentialNode([], []);
      $newParent = $this->parent->replaceAtPos($newParent, $index);
      $newParent->pushChild($this);
    }

    return $this->parent->insertChildAtPos($newNode, $index);
  }

  public function insertAsLastSibling($newNode)
  {
    $index = $this->getIndex();
    if (is_null($index)) {
      throw new VisibleSystemException('Trying to insert a brother of the root');
    }

    // Ensure parent is a seq node
    if (!$this->parent instanceof SequentialNode) {
      $newParent = new SequentialNode([], []);
      $newParent = $this->parent->replaceAtPos($newParent, $index);
      $newParent->pushChild($this);
    }

    return $this->parent->insertChildAtPos($newNode, count($this->parent->children));
  }

  public function insertChildAtPos($node, $index)
  {
    array_splice($this->children, $index + 1, 0, [$node]);
    $node->attach($this);
    return $node;
  }

  public function unshiftChild($child)
  {
    array_unshift($this->children, $child);
    $child->attach($this);
  }

  public function getParent()
  {
    return $this->parent;
  }

  public function getChildren()
  {
    return $this->children;
  }

  public function countChildren()
  {
    return count($this->children);
  }

  public function toArray()
  {
    return array_merge($this->info, [
      'children' => \array_map(function ($child) {
        return $child->toArray();
      }, $this->children),
    ]);
  }

  protected function childrenReduceAnd($callable)
  {
    return \array_reduce(
      $this->children,
      function ($acc, $child) use ($callable) {
        return $acc && $callable($child);
      },
      true
    );
  }

  protected function childrenReduceOr($callable)
  {
    return \array_reduce(
      $this->children,
      function ($acc, $child) use ($callable) {
        return $acc || $callable($child);
      },
      false
    );
  }

  /**
   * The description of the node is the sequence of description of its children, separated by a separator
   */
  public function getDescription()
  {
    $i = 0;
    $desc = [];
    $args = [];

    if (isset($this->info['customDescription'])) {
      return $this->info['customDescription'];
    }

    foreach ($this->children as $child) {
      $name = 'action' . $i++;
      $tmp = $child->getDescription();
      if ($tmp != '') {
        $args[$name] = $tmp;
        $args['i18n'][] = $name;

        $args[$name] = $tmp;
        $args['i18n'][] = $name;
        $desc[] = '${' . $name . '}';
      }
    }

    return [
      'log' => \implode($this->getDescriptionSeparator(), $desc),
      'args' => $args,
    ];
  }

  public function getDescriptionSeparator()
  {
    return '';
  }

  /***********************
   *** Getters (sugar) ***
   ***********************/
  public function getInfo()
  {
    return $this->info;
  }

  public function getState()
  {
    return $this->info['state'] ?? null;
  }

  public function getPlayerId()
  {
    return $this->info['playerId'] ?? null;
  }

  public function getType()
  {
    return $this->info['type'] ?? Engine::NODE_LEAF;
  }

  public function getFlag()
  {
    return $this->info['flag'] ?? null;
  }

  public function getArgs()
  {
    return $this->info['args'] ?? null;
  }

  public function setArgs($newArgs)
  {
    $this->info['args'] = $newArgs;
  }

  public function getCardId()
  {
    return $this->info['cardId'] ?? null;
  }

  public function getSource()
  {
    return $this->info['source'] ?? null;
  }

  public function getSourceId()
  {
    return $this->info['sourceId'] ?? null;
  }

  public function isDoable($player)
  {
    return true;
  }
  public function getUndoableMandatoryNode($player)
  {
    $playerId = $this->getPlayerId();
    if (!is_null($playerId) && $playerId != $player->getId()) {
      return null;
    }

    if (!$this->isResolved() && !$this->isDoable($player) && ($this->isMandatory() || !$this->isOptional())) {
      return $this;
    }
    return null;
  }

  /***********************
   *** Node resolution ***
   ***********************/
  public function isResolved()
  {
    return isset($this->info['resolved']) && $this->info['resolved'];
  }

  public function getResolutionArgs()
  {
    return $this->info['resolutionArgs'] ?? null;
  }

  public function getNextUnresolved()
  {
    if ($this->isResolved()) {
      return null;
    }

    if (!isset($this->info['choice']) || $this->children[$this->info['choice']]->isResolved()) {
      return $this;
    } else {
      return $this->children[$this->info['choice']]->getNextUnresolved();
    }
  }

  public function resolve($args)
  {
    $this->info['resolved'] = true;
    $this->info['resolutionArgs'] = $args;
  }

  // Useful for zombie players
  public function clearZombieNodes($playerId)
  {
    foreach ($this->children as $child) {
      $child->clearZombieNodes($playerId);
    }

    if ($this->getPlayerId() == $playerId) {
      $this->resolve(Engine::ZOMBIE);
    }
  }

  /********************
   *** Node choices ***
   ********************/
  public function areChildrenOptional()
  {
    return false;
  }

  public function isOptional()
  {
    return $this->info['optional'] ?? $this->parent != null && $this->parent->areChildrenOptional();
  }

  public function isCancellable()
  {
    return $this->info['cancellable'] ?? false;
  }

  public function isAutomatic($player = null)
  {
    $choices = $this->getChoices($player);
    return count($choices) < 2;
  }

  // Allow for automatic resolution in parallel node
  public function isIndependent($player = null)
  {
    return $this->isAutomatic($player) &&
      $this->childrenReduceAnd(function ($child) use ($player) {
        return $child->isIndependent($player);
      });
  }

  public function getChoices($player = null, $displayAllChoices = false)
  {
    $choice = null;
    $choices = [];
    $children = $this->getType() == Engine::NODE_SEQUENTIAL && !empty($this->children) ? [0 => $this->children[0]] : $this->children;

    foreach ($children as $id => $child) {
      if (!$child->isResolved() && ($displayAllChoices || $child->isDoable($player))) {
        $choice = [
          'id' => $id,
          'description' => $this->getType() == Engine::NODE_SEQUENTIAL ? $this->getDescription() : $child->getDescription(),
          'args' => $child->getArgs(),
          'optionalAction' => $child->isOptional(),
          'automaticAction' => $child->isAutomatic($player),
          'independentAction' => $child->isIndependent($player),
          'irreversibleAction' => $child->isIrreversible($player),
          'source' => $child->getSource(),
          'sourceId' => $child->getSourceId(),
        ];
        $choices[$id] = $choice;
      }
    }

    if ($this->isOptional()) {
      //This is a bit convoluted but here is the brakdown. If current node is optional, we add pass:
      // - if more than one child
      // - if only one child we don't want it to be non optional as player can get stuck if we automatically choose it
      // - if only child is optional we can safely go there because player can always pass later
      // - if the only child is automatic, we want player to skip the automatic child if he wants to pass
      if (count($choices) != 1 || !$choice['optionalAction'] || $choice['automaticAction']) {
        $choices[Engine::PASS] = [
          'id' => Engine::PASS,
          'description' => clienttranslate('Pass'),
          'irreversibleAction' => false,
          'args' => [],
        ];
      }
    }

    if ($this->isCancellable()) {
      $choices[Engine::CANCEL] = [
        'id' => Engine::CANCEL,
        'description' => clienttranslate('Cancel'),
        'irreversibleAction' => false,
        'args' => [],
      ];
    }

    return $choices;
  }

  public function choose($childIndex, $auto = false)
  {
    $this->info['choice'] = $childIndex;
    $child = $this->children[$this->info['choice']];
    if (!$auto && !($child instanceof \Bga\Games\sanctuary\Framework\Engine\LeafNode)) {
      $child->enforceMandatory();
    }
  }

  public function unchoose()
  {
    unset($this->info['choice']);
  }

  /************************
   ***** Reversibility *****
   ************************/
  public function isIrreversible($player = null)
  {
    return false;
  }

  /************************
   *** Action resolution ***
   ************************/
  public function isActionResolved()
  {
    return $this->info['actionResolved'] ?? false;
  }

  public function getActionResolutionArgs()
  {
    return $this->info['actionResolutionArgs'] ?? null;
  }

  public function resolveAction($args)
  {
    $this->info['actionResolved'] = true;
    $this->info['actionResolutionArgs'] = $args;
    $this->info['optional'] = false;
  }

  public function getResolvedActions($types)
  {
    $actions = [];
    if (in_array($this->getState(), $types) && $this->isActionResolved()) {
      $actions[] = $this;
    }
    foreach ($this->children as $child) {
      $actions = array_merge($actions, $child->getResolvedActions($types));
    }
    return $actions;
  }

  public function getNextSibling()
  {
    $id = $this->getIndex();
    $children = $this->getParent()->getChildren();
    return $children[$id + 1];
  }

  public function enforceMandatory()
  {
    $this->info['mandatory'] = true;
  }

  public function isMandatory()
  {
    return $this->info['mandatory'] ?? false;
  }
}
