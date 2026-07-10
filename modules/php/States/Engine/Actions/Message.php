<?php

declare(strict_types=1);

namespace Bga\Games\Sanctuary\States\Engine\Actions;

use Bga\GameFramework\StateType;
use Bga\Games\Sanctuary\Framework\Engine\AbstractNode;
use Bga\Games\Sanctuary\Framework\Engine\AutomaticActionState;
use Bga\Games\Sanctuary\Framework\Engine\Constants\States;
use Bga\Games\Sanctuary\Game;

class Message extends AutomaticActionState
{
    function __construct(
        protected Game $game,
        protected ?AbstractNode $node = null
    ) {
        parent::__construct(
            $game,
            node: $node,
            id: States::ST_MESSAGE,
            type: StateType::GAME,
        );
    }

    function onEnteringState(int $activePlayerId)
    {
        $this->notify->all("message", $this->getNodeArgs("message", ""), $this->getNodeArgs("args", []));
        return $this->resolve();
    }
}
