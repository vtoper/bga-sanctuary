<?php

declare(strict_types=1);

namespace Bga\Games\sanctuary\Framework\Engine;

use Bga\GameFramework\States\PossibleAction;
use Bga\GameFramework\StateType;
use Bga\GameFramework\VisibleSystemException;
use Bga\Games\sanctuary\Framework\Db\Log;
use Bga\Games\sanctuary\Framework\Managers\Config;
use Bga\Games\sanctuary\Game;
use Bga\Games\sanctuary\Managers\Players;


class ActionState extends AutomaticActionState
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

    final public function getArgs(int $activePlayerId): array
    {
        return array_merge(
            $this->getGeneralArgs($activePlayerId),
            $this->getActionArgs($activePlayerId),
        );
    }

    protected function getGeneralArgs(int $activePlayerId): array
    {
        $args = [];

        $isOptional = $this->isOptional();

        if (is_null($isOptional)) {
            $isOptional = false;
            if ($this->getNode() !== null && $this->getNode()->getType() === Engine::NODE_LEAF) {
                $isOptional = $this->getNode()->isOptional();
            }
        }

        $args['optionalAction'] = $isOptional;

        if ($this->getCustomStateDescription() !== null) {
            $args['customStateDescription'] = $this->getCustomStateDescription();
        }

        $args['anytimeActions'] = $this->getAnytimeActions();

        return $args;
    }

    protected function getActionArgs(int $activePlayerId): array
    {
        return [];
    }

    private function getAnytimeActions(): array
    {
        if (!$this->getNodeArgs("addAnytimeActions", true)) {
            return [];
        }

        $freeActionsDir = __DIR__ . '/../../States/Actions/Anytime/';
        $namespace = 'Bga\Games\sanctuary\States\Actions\Anytime\\';

        $actions = [];
        foreach (glob($freeActionsDir . '*.php') as $file) {
            $actions[] = ['state' => $namespace . basename($file, '.php')];
        }

        $anytimeActions = [];
        foreach ($actions as $action) {
            $tree = Engine::buildTree($action);
            if ($tree->isDoable(Players::getActiveId())) {
                $anytimeActions[] = [
                    'description' => $tree->getDescription(true),
                    "id" => $action["state"]
                ];
            }
        }

        return $anytimeActions;
    }

    #[PossibleAction]
    public function actPassOptionalAction()
    {
        if (!$this->getNode()->isOptional()) {
            throw new VisibleSystemException('This action is not optional');
        }

        return Engine::resolve(Engine::PASS);
    }

    #[PossibleAction]
    public function actAnytimeAction(string $actionId)
    {
        Log::step();

        Config::incEngineChoices();
        Engine::prependAtRoot([
            "state" => $actionId,
            "args" => [
                "addAnytimeActions" => false
            ]
        ]);

        return Engine::proceed();
    }

    function zombie(int $playerId)
    {
        $this->notify->all("message", clienttranslate('${player_name} is a zombie, skipping their turn'), [
            "player_id" => $playerId,
        ]);
        return $this->resolve([
            "automatic" => true
        ]);
    }
}
