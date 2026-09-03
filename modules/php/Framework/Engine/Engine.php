<?php

namespace Bga\Games\sanctuary\Framework\Engine;

use Bga\GameFramework\VisibleSystemException;
use Bga\Games\sanctuary\Framework\Db\Log;
use Bga\Games\sanctuary\Framework\Engine\Constants\States;
use Bga\Games\sanctuary\Framework\Managers\Config;
use Bga\Games\sanctuary\Framework\Managers\Players;
use Bga\Games\sanctuary\Framework\TurnOrderManager;
use Bga\Games\sanctuary\Game;
use Bga\Games\sanctuary\States\Engine\ConfirmPartialTurn;
use Bga\Games\sanctuary\States\Engine\ConfirmTurn;
use Bga\Games\sanctuary\States\Engine\ResolveChoice;

/*
 * Engine: a class that allows to handle complex flow
 */

class Engine
{
    public static $tree = null;
    public static ?AbstractNode $currentNode = null;

    public static function boot()
    {
        $tree = Config::getEngine();
        self::$tree = self::buildTree($tree);
        self::ensureSeqRootNode();
    }

    /**
     * Convert an array into a tree
     * @param array $tree
     */
    public static function buildTree($tree)
    {
        $tree['children'] = $tree['children'] ?? [];
        $type = $tree['type'] ?? (empty($tree['children']) ? self::NODE_LEAF : self::NODE_SEQUENTIAL);

        $children = [];
        foreach ($tree['children'] as $child) {
            $children[] = self::buildTree($child);
        }

        $className = __NAMESPACE__ . '\\' . ucfirst($type) . 'Node';
        unset($tree['children']);
        return new $className($tree, $children);
    }

    /**
     * Ensure the root is a SEQ node to be able to insert easily in the current flow
     */
    protected static function ensureSeqRootNode()
    {
        if (!self::$tree instanceof SequentialNode) {
            $isCurrentNode = self::$currentNode == self::$tree;
            self::$tree = new SequentialNode([], [self::$tree]);
            if ($isCurrentNode) {
                self::$currentNode = self::$tree;
            }
            self::save();
        }
    }

    /**
     * Save current tree into Globals table
     */

    public static function save()
    {
        $t = self::$tree->toArray();
        Config::setLastEngine(Config::getEngine());
        Config::setEngine($t);
    }

    /**
     * Setup the engine, given an array representing a tree
     * @param array $t
     */
    public static function setup($tree, $endCallback)
    {
        self::$tree = self::buildTree($tree);
        self::save();
        Config::setEndEngineCallback($endCallback);
        Config::setEngineChoices(0);
        Log::enable();
        Log::startEngine();
    }

    /**
     * Proceed to next unresolved part of tree
     */
    public static function proceed($confirmedPartial = false, $isUndo = false)
    {
        $node = self::getNextUnresolved(true);

        // Are we done ?
        if ($node == null) {
            if (Config::getEngineChoices() == 0) {
                return self::confirm(); // No choices were made => auto confirm
            } else {
                // Confirm/restart
                return ConfirmTurn::class;
            }
            return;
        }

        $oldPlayerId = Game::get()->getActivePlayerId();
        $playerId = $node->getPlayerId();

        //TODO handle multiple with specific node type
        $player = Players::get($playerId === self::PLAYER_ID_AUTO ? null : $playerId);
        if (
            $playerId != null &&
            $oldPlayerId != $playerId &&
            !$node->isIndependent($player) &&
            Config::getEngineChoices() != 0 &&
            !$confirmedPartial
        ) {
            return ConfirmPartialTurn::class;
        }

        // Jump to resolveStack state to ensure we can change active playerId
        if ($playerId != null && $oldPlayerId != $playerId && $playerId != self::PLAYER_ID_AUTO) {
            Game::get()->gamestate->jumpToState(States::ST_GENERIC_GAME_STATE);
            Game::get()->gamestate->changeActivePlayer($playerId);
        }

        if ($confirmedPartial) {
            Log::enable();
            Log::checkpoint();
            Config::setEngineChoices(0);
        }

        // If node with choice, switch to choice state
        $possibleChoices = $node->getChoices($player);
        $allChoices = $node->getChoices($player, true);

        if (!empty($allChoices) && $node->getType() != self::NODE_LEAF) {
            // Only one choice : auto choose
            $id = array_keys($possibleChoices)[0] ?? null;
            if (
                count($possibleChoices) == 1 &&
                count($allChoices) == 1 &&
                array_keys($allChoices) == array_keys($possibleChoices) &&
                !$possibleChoices[$id]['irreversibleAction']
            ) {
                return self::chooseNode($player, $id, true);
            } else {
                return ResolveChoice::class;
            }
        } else {
            // No choice => proceed to do the action
            return $node->getState();
        }
    }

    /**
     * Confirm the full resolution of current flow
     */
    public static function confirm()
    {
        $node = self::getNextUnresolved();
        // Are we done ?
        if ($node != null) {
            throw new VisibleSystemException("You can't confirm an ongoing turn");
        }

        // Callback
        return self::terminateEngine();
    }

    public static function terminateEngine()
    {
        $callback = Config::getEndEngineCallback();
        if (isset($callback['state'])) {
            return $callback['state'];
        } elseif (isset($callback['order'])) {
            return TurnOrderManager::proceed($callback['order']);
        } elseif (is_callable($callback)) {
            return call_user_func_array($callback, []);
        }

        throw new VisibleSystemException("Invalid end engine callback");
    }

    /**
     * Choose one option
     */
    public static function chooseNode($player, $nodeId, $auto = false)
    {
        $node = self::getNextUnresolved();
        if ($node === null) {
            throw new VisibleSystemException('No next node');
        }
        $args = $node->getChoices($player);
        if (!isset($args[$nodeId])) {
            throw new VisibleSystemException('This choice is not possible');
        }

        if (!$auto) {
            Config::incEngineChoices();
            Log::step();
        }

        if ($nodeId == self::PASS) {
            self::resolve(self::PASS);
            return self::proceed();
        }

        if ($nodeId == self::CANCEL) {
            return self::terminateEngine();
        }

        if ($node->getChildren()[$nodeId]->isResolved()) {
            throw new VisibleSystemException('Node is already resolved');
        }

        $node->choose($nodeId, $auto);
        self::save();
        return self::proceed();
    }

    /**
     * Recursively compute the next unresolved node we are going to address
     */
    public static function getNextUnresolved($invalidate = false)
    {
        if (self::$currentNode != null && !$invalidate) {
            return self::$currentNode;
        }

        self::$currentNode = self::$tree->getNextUnresolved();
        return self::$currentNode;
    }

    /**
     * Get the list of choices of current node
     */
    public static function getNextChoices($player = null, $displayAllChoices = false)
    {
        return self::getNextUnresolved()->getChoices($player, $displayAllChoices);
    }

    public static function resolve($args = [])
    {
        self::doResolve($args, false, false);

        return Engine::proceed();
    }

    public static function autoResolve($args = [])
    {
        self::doResolve($args, false, true);
        return Engine::proceed();
    }

    public static function resolveIrreversible($args = [])
    {
        self::doResolve($args, true, false);
        return Engine::proceed();
    }

    public static function autoResolveIrreversible($args = [])
    {
        self::doResolve($args, true, true);
        return Engine::proceed();
    }

    public static function doResolve($args = [], $checkpoint = false, $auto = false)
    {
        $node = self::getNextUnresolved();

        $node->resolve($args);
        self::save();

        if (!$auto && !$node->isAutomatic()) {
            Config::incEngineChoices();
        }

        if ($checkpoint) {
            self::checkpoint();
        }
    }

    public static function resolveAction($args = [])
    {
        self::doResolveAction($args, false, false);

        return Engine::proceed();
    }

    public static function autoResolveAction($args = [])
    {
        self::doResolveAction($args, false, true);
        return Engine::proceed();
    }

    public static function resolveIrreversibleAction($args = [])
    {
        self::doResolveAction($args, true, false);
        return Engine::proceed();
    }

    public static function autoResolveIrreversibleAction($args = [])
    {
        self::doResolveAction($args, true, true);
        return Engine::proceed();
    }

    public static function doResolveAction($args = [], $checkpoint = false, $auto = false)
    {
        $node = self::getNextUnresolved();

        $node->resolveAction($args);
        self::save();

        if (!$auto) {
            Config::incEngineChoices();
        }

        if ($checkpoint) {
            self::checkpoint();
        }
    }

    public static function checkpoint()
    {
        Config::setEngineChoices(0);
        Log::checkpoint();
    }

    /**
     * Insert a new node at root level at the end of seq node
     */
    public static function appendAtRoot($t, $last = true)
    {
        self::ensureSeqRootNode();
        $node = self::buildTree($t);
        self::$tree->pushChild($node);
        self::save();
        return $node;
    }

    /**
     * Insert a new node at root level at the beginning of seq node
     */
    public static function prependAtRoot($t, $last = true)
    {
        self::ensureSeqRootNode();
        $node = self::buildTree($t);
        self::$tree->unshiftChild($node);
        self::save();
        return $node;
    }

    /**
     * Insert a new node at root level at the end of seq node
     */
    public static function insertAtRoot($t, $last = true)
    {
        self::ensureSeqRootNode();
        $node = self::buildTree($t);
        if ($last) {
            self::$tree->pushChild($node);
        } else {
            self::$tree->unshiftChild($node);
        }
        self::save();
        return $node;
    }

    /**
     * Get the "next after finishing action node", create a new if needed
     */
    public static function getAfterFinishingNode($fromNode = null)
    {
        self::ensureSeqRootNode();

        if ($fromNode === null) {
            // Go through root children
            $childs = self::$tree->getChildren();
            for ($i = 0; $i < count($childs); $i++) {
                if ($childs[$i]->getFlag() == self::AFTER_FINISHING_ACTION) {
                    return $childs[$i];
                }
            }

            return self::insertAtRoot([
                'type' => self::NODE_PARALLEL,
                'flag' => self::AFTER_FINISHING_ACTION,
                'childs' => [],
            ]);
        } else {
            // Search following siblings at each level, then each ancestor.
            $node = $fromNode;

            while ($node !== null) {
                if ($node->getFlag() == self::AFTER_FINISHING_ACTION) {
                    return $node;
                }

                $parent = $node->getParent();
                if ($parent === null) {
                    break;
                }

                $siblings = $parent->getChildren();
                $nodeIndex = $node->getIndex();
                for ($index = $nodeIndex + 1; $index < count($siblings); $index++) {
                    if ($siblings[$index]->getFlag() == self::AFTER_FINISHING_ACTION) {
                        return $siblings[$index];
                    }
                }

                $node = $parent;
            }

            return self::getAfterFinishingNode();
        }
    }

    /**
     * Insert after finishing action
     */
    public static function pushAfterFinishingChilds(array $childs, $fromCurrentNode = false)
    {
        if (empty($childs)) {
            return;
        }

        $node = self::getAfterFinishingNode($fromCurrentNode ? self::getNextUnresolved() : null);
        foreach ($childs as $child) {
            $node->pushChild(self::buildTree($child));
        }
    }

    /**
     * insertAsChild: turn the node into a SEQ if needed, then insert the flow tree as a child
     */
    public static function insertAsChild($t, &$node = null)
    {
        if (is_null($t)) {
            return;
        }
        if (is_null($node)) {
            $node = self::getNextUnresolved();
        }

        // If the node is an action leaf, turn it into a SEQ node first
        if ($node->getType() == self::NODE_LEAF) {
            $newNode = $node->toArray();
            $newNode['type'] = self::NODE_SEQUENTIAL;
            $node = $node->replace(self::buildTree($newNode));
            self::$currentNode = $node;
        }

        // Push child
        $node->pushChild(self::buildTree($t));
        self::save();
    }

    public static function insertAsSibling($t, &$node = null)
    {
        if (is_null($t)) {
            return;
        }
        if (is_null($node)) {
            $node = self::getNextUnresolved();
        }

        $node->insertAsBrother(self::buildTree($t));
        self::save();
    }

    /**
     * insertOrUpdateParallelChilds:
     *  - if the node is a parallel node => insert all the nodes as childs
     *  - if one of the child is a parallel node => insert as their childs instead
     *  - otherwise, make the action a parallel node
     */

    public static function insertOrUpdateParallelChilds($childs, &$node = null)
    {
        if (empty($childs)) {
            return;
        }
        if (is_null($node)) {
            $node = self::$tree->getNextUnresolved();
        }

        if ($node->getType() == self::NODE_SEQUENTIAL) {
            // search if we have children and if so if we have a parallel node
            foreach ($node->getChilds() as $child) {
                if ($child->getType() == self::NODE_PARALLEL) {
                    foreach ($childs as $newChild) {
                        $child->pushChild(self::buildTree($newChild));
                    }
                    self::save();
                    return;
                }
            }

            $node->pushChild(
                self::buildTree([
                    'type' => self::NODE_PARALLEL,
                    'childs' => $childs,
                ])
            );
        }
        // Otherwise, turn the node into a PARALLEL node if needed, and then insert the childs
        else {
            // If the node is an action leaf, turn it into a Parallel node first
            if ($node->getType() == self::NODE_LEAF) {
                $newNode = $node->toArray();
                $newNode['type'] = self::NODE_PARALLEL;
                $node = $node->replace(self::buildTree($newNode));
            }

            // Push childs
            foreach ($childs as $newChild) {
                $node->pushChild(self::buildTree($newChild));
            }
            self::save();
        }
    }

    public static function confirmPartialTurn()
    {
        $node = self::getNextUnresolved();

        // Are we done ?
        if ($node == null) {
            throw new VisibleSystemException("You can't partial confirm an ended turn");
        }

        $oldPlayerId = Game::get()->getActivePlayerId();
        $playerId = $node->getPlayerId();

        if ($oldPlayerId == $playerId) {
            throw new VisibleSystemException("You can't partial confirm for the same player");
        }

        // Clear log
        self::checkpoint();
        return Engine::proceed(true);
    }

    /**
     * Restart the whole flow
     */
    public static function restart()
    {
        if (Config::getEngineChoices() < 1) {
            throw new VisibleSystemException('No choice to undo');
        }

        Log::undoTurn();

        // Force to clear cached informations
        Config::fetch();
        self::boot();

        return self::proceed(false);
    }

    /**
     * Restart at a given step
     */
    public static function undoToStep($stepId)
    {
        Log::undoToStep($stepId);

        // Force to clear cached informations
        Config::fetch();
        self::boot();
        return self::proceed(false);
    }

    /*
     ██████╗ ██████╗ ███╗   ██╗███████╗████████╗ █████╗ ███╗   ██╗████████╗███████╗
    ██╔════╝██╔═══██╗████╗  ██║██╔════╝╚══██╔══╝██╔══██╗████╗  ██║╚══██╔══╝██╔════╝
    ██║     ██║   ██║██╔██╗ ██║███████╗   ██║   ███████║██╔██╗ ██║   ██║   ███████╗
    ██║     ██║   ██║██║╚██╗██║╚════██║   ██║   ██╔══██║██║╚██╗██║   ██║   ╚════██║
    ╚██████╗╚██████╔╝██║ ╚████║███████║   ██║   ██║  ██║██║ ╚████║   ██║   ███████║
     ╚═════╝ ╚═════╝ ╚═╝  ╚═══╝╚══════╝   ╚═╝   ╚═╝  ╚═╝╚═╝  ╚═══╝   ╚═╝   ╚══════╝

    */
    const NODE_SEQUENTIAL = 'sequential';
    const NODE_OR = 'or';
    const NODE_XOR = 'xor';
    const NODE_PARALLEL = 'parallel';
    const NODE_LEAF = 'leaf';
    const AFTER_FINISHING_ACTION = 'afterFinishing';

    const CANCEL = 97;
    const ZOMBIE = 98;
    const PASS = 99;

    const PLAYER_ID_AUTO = 'auto';
}
