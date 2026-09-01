<?php

declare(strict_types=1);

namespace Bga\Games\Sanctuary\States\Effects;

use Bga\GameFramework\Actions\Types\JsonParam;
use Bga\GameFramework\StateType;
use Bga\GameFramework\States\PossibleAction;
use Bga\GameFramework\UserException;
use Bga\Games\Sanctuary\Constants\States;
use Bga\Games\Sanctuary\Framework\Engine\AbstractNode;
use Bga\Games\Sanctuary\Framework\Engine\ActionStateWithRevert;
use Bga\Games\Sanctuary\Game;
use Bga\Games\Sanctuary\Managers\Players;
use Bga\Games\Sanctuary\Managers\Tiles;

class Pouch extends ActionStateWithRevert
{
    function __construct(
        protected Game $game,
        protected ?AbstractNode $node = null
    ) {
        parent::__construct(
            $game,
            node: $node,
            id: States::ST_POUCH,
            type: StateType::ACTIVE_PLAYER,
            description: clienttranslate('${actplayer} may discard up to ${n} tile(s) to gain ${n} pouch marker(s)'),
            descriptionMyTurn: clienttranslate('${you} may discard up to ${n} tile(s) to gain ${n} pouch marker(s)'),
        );
    }

    public function getActionArgs(int $activePlayerId): array
    {
        return [
            'n' => $this->getNodeArgs('n', 1),
            '_private' => [
                $activePlayerId => [
                    'cardIds' => Players::get($activePlayerId)->getHand()->getIds(),
                ],
            ],
            '_merge_private' => true,
        ];
    }

    #[PossibleAction]
    public function actPouch(#[JsonParam(associative: null)] array $cardIds)
    {
        $player = Players::getCurrent();
        if ($player != Players::getActive()) {
            throw new UserException(clienttranslate('Not your turn'));
        }

        $args = $this->getActionArgs($player->getId());
        if (count($cardIds) > $args['n'] || count(array_unique($cardIds)) !== count($cardIds)) {
            throw new UserException(clienttranslate('You cannot discard that many tiles'));
        }
        foreach ($cardIds as $cardId) {
            if (!in_array($cardId, $args['_private'][$player->getId()]['cardIds'], true)) {
                throw new UserException(clienttranslate('This tile cannot be discarded'));
            }
        }

        [$discarded] = Tiles::discard($cardIds);
        $pouchGained = count($cardIds);
        $player->incPouch($pouchGained);

        $this->notify->all('pouchGained', clienttranslate('${player_name} discards ${n} tile(s) and gains ${n} pouch marker(s)'), [
            'player' => $player,
            'n' => $pouchGained,
            'cardIds' => $discarded->getIds(),
            'pouch' => $player->getPouch(),
        ]);

        return $this->resolve(['n' => $pouchGained]);
    }

    function zombie(int $playerId)
    {
        return $this->resolve(['n' => 0]);
    }
}
