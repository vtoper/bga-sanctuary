<?php

declare(strict_types=1);

namespace Bga\Games\sanctuary\Framework\Engine;

use Bga\GameFramework\StateType;
use Bga\Games\sanctuary\Game;
use Bga\Games\sanctuary\Managers\Players;
use Bga\Games\sanctuary\Framework\Models\Player;


class AutomaticActionState extends \Bga\GameFramework\States\GameState
{
    function __construct(
        protected Game $game,
        public int $id,
        public \Bga\GameFramework\StateType $type,
        public ?string $name = null,
        public string $description = '',
        public string $descriptionMyTurn = '',
        public array $transitions = [],
        public bool $updateGameProgression = false,
        public string|int|null $initialPrivate = null,
        protected ?AbstractNode $node = null
    ) {
        parent::__construct(
            $game,
            id: $id,
            type: $type,
            name: $name,
            description: $description,
            descriptionMyTurn: $descriptionMyTurn,
            transitions: $transitions,
            updateGameProgression: $updateGameProgression,
            initialPrivate: $initialPrivate,
        );
    }

    public function getCustomStateDescription()
    {
        return null;
    }

    public function getNode()
    {
        if ($this->node !== null) {
            return $this->node;
        }

        return Engine::getNextUnresolved();
    }

    public function getNodeArgs($field = null, $default = null)
    {
        $args = [];

        $node = $this->getNode();
        if ($node !== null) {
            $args = $node->getArgs() ?? [];
        }

        if ($field !== null) {
            return $args[$field] ?? $default;
        }

        return $args;
    }

    public function isAutomatic()
    {
        return $this->type === StateType::GAME;
    }

    public function isOptional()
    {
        return null;
    }

    public function isIrreversible()
    {
        return $this->getNode()->getInfo()["irreversible"] ?? false;
    }

    public function isDoable(int|Player $playerId): bool
    {
        return true;
    }

    public function isIndependent()
    {
        return $this->getNode()->getInfo()["independent"] ?? false;
    }

    public function getDescription()
    {
        return null;
    }

    protected function getPlayerId()
    {
        $argsPlayerId = $this->getNodeArgs("playerId");
        return $argsPlayerId ?? Players::getActiveId();
    }

    protected function getPlayer()
    {
        return Players::get($this->getPlayerId());
    }

    protected function resolve($args = [])
    {
        if ($this->isAutomatic() || ($args["automatic"] ?? false)) {
            return Engine::autoResolveAction($args);
        } else {
            return Engine::resolveAction($args);
        }
    }

    protected function resolveIrreversible($args = [])
    {
        if ($this->isAutomatic() || ($args["automatic"] ?? false)) {
            return Engine::autoResolveIrreversibleAction($args);
        } else {
            return Engine::resolveIrreversibleAction($args);
        }
    }
}
