<?php

namespace Bga\Games\sanctuary\Framework\Engine;

use Bga\GameFramework\VisibleSystemException;
use Bga\Games\sanctuary\Game;

/*
 * Leaf: a class that represent a Leaf
 */

class LeafNode extends AbstractNode
{
    public function __construct($info = [])
    {
        parent::__construct($info, []);
        $this->info['type'] = Engine::NODE_LEAF;
    }

    /**
     * An action leaf is resolved as soon as the action is resolved
     */
    public function isResolved()
    {
        return parent::isResolved() || ($this->getState() != null && $this->isActionResolved());
    }

    public function isAutomatic($player = null)
    {
        return $this->getStateInstance()->isAutomatic($player);
    }

    public function isIndependent($player = null)
    {
        return $this->getStateInstance()->isIndependent($player);
    }

    public function isOptional()
    {
        return $this->getStateInstance()->isOptional() ?? false;
    }

    public function isIrreversible($player = null)
    {
        return $this->getStateInstance()->isIrreversible($player);
    }

    /**
     * A Leaf is doable if the corresponding action is doable by the player
     */
    public function isDoable($player)
    {
        // Useful for a SEQ node where the 2nd node might become doable thanks to the first one
        if (isset($this->info['willBeDoable'])) {
            return true;
        }
        // Edge case when searching undoable mandatory node pending
        if ($this->isResolved()) {
            return true;
        }
        if (isset($this->info['state'])) {
            return $this->getStateInstance()->isDoable($player);
        }
        var_dump($this->parent->toArray());
        throw new VisibleSystemException('Unimplemented isDoable function for non-action Leaf');
    }

    /**
     * The description is given by the corresponding action
     */
    public function getDescription()
    {
        if (isset($this->info['customDescription'])) {
            return $this->info['customDescription'];
        }

        $stateDescription = $this->getStateInstance()->getDescription();

        if ($stateDescription) {
            return $stateDescription;
        }

        return parent::getDescription();
    }

    private function getStateInstance()
    {
        $stateClass = $this->getState();
        return new $stateClass(Game::get(), node: $this);
    }
}
