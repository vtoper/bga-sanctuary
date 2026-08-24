<?php

declare(strict_types=1);

namespace Bga\Games\Sanctuary\States\Actions;

use Bga\GameFramework\StateType;
use Bga\GameFramework\States\GameState;
use Bga\GameFramework\States\PossibleAction;
use Bga\GameFramework\UserException;
use Bga\Games\Sanctuary\Game;
use Bga\Games\Sanctuary\Constants\States;
use Bga\Games\Sanctuary\Framework\Db\Log;
use Bga\Games\Sanctuary\Framework\Engine\AbstractNode;
use Bga\Games\Sanctuary\Framework\Engine\AutomaticActionState;
use Bga\Games\Sanctuary\Managers\Players;
use Bga\Games\Sanctuary\Managers\Tiles;
use Bga\Games\Sanctuary\Framework\Engine\Engine;

class DrawTile extends AutomaticActionState
{
    function __construct(
        protected Game $game,
        protected ?AbstractNode $node = null
    ) {
        parent::__construct(
            $game,
            node: $node,
            id: States::ST_DRAW_TILE,
            type: StateType::GAME,
        );
    }

    public function getCustomStateDescription()
    {
        if (!is_null($this->getSource())) {
            return [
                "description" => clienttranslate('${actplayer} must draw ${n} tile(s) (${source})'),
                "descriptionMyTurn" => clienttranslate('${you} must draw ${n} tile(s) (${source})'),
            ];
        }
        return null;
    }

    public function getDescription()
    {
        if (!is_null($this->getSource())) {
            return [
                "log" => clienttranslate('Draw ${n} tile(s) (${source})'),
                "args" => [
                    "n" => $this->getNodeArgs("n", 1),
                    "source" => $this->getSource() ?? ""
                ]
            ];
        }
        return  [
            'log' => clienttranslate('Draw ${n} tile(s)'),
            'args' => ['n' => $this->getNodeArgs("n", 1)]
        ];
    }


    public function getActionArgs(int $activePlayerId): array {}


    public function isAutomatic($player = null)
    {
        return true;
    }

    public function isIrreversible($player = null)
    {
        return true;
    }

    function onEnteringState(int $activePlayerId)
    {
        $n = $this->getNodeArgs("n", 1);
        $player = Players::get($activePlayerId);
        $source = $this->getSource() ?? "";
        $drawnTiles = Tiles::draw($player, $n);
        Engine::checkpoint();
        return $this->resolve(['cards' => $drawnTiles]);
    }
}
