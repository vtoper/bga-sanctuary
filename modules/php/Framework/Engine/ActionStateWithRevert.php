<?php

declare(strict_types=1);

namespace Bga\Games\sanctuary\Framework\Engine;

use Bga\GameFramework\States\PossibleAction;
use Bga\Games\sanctuary\Framework\Db\Log;
use Bga\Games\sanctuary\Framework\Managers\Config;
use Bga\Games\sanctuary\Game;

class ActionStateWithRevert extends ActionState
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
            node: $node
        );
    }

    protected function getGeneralArgs(int $activePlayerId): array
    {
        return array_merge(parent::getGeneralArgs($activePlayerId), [
            'previousEngineChoices' => Config::getEngineChoices(),
            'previousSteps' => Log::getUndoableSteps(),
            'automaticAction' => false,
        ]);
    }

    #[PossibleAction]
    public function actRestart()
    {
        return Engine::restart();
    }

    #[PossibleAction]
    public function actUndoToStep(int $stepId)
    {
        return Engine::undoToStep($stepId);
    }
}
