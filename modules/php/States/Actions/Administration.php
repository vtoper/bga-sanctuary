<?php

declare(strict_types=1);

namespace Bga\Games\Sanctuary\States\Actions;

use Bga\GameFramework\Actions\Types\JsonParam;
use Bga\GameFramework\StateType;
use Bga\GameFramework\States\GameState;
use Bga\GameFramework\States\PossibleAction;
use Bga\GameFramework\UserException;
use Bga\Games\Sanctuary\Game;
use Bga\Games\Sanctuary\Constants\States;
use Bga\Games\Sanctuary\Framework\Db\Log;
use Bga\Games\Sanctuary\Framework\Engine\AbstractNode;
use Bga\Games\sanctuary\Framework\Engine\ActionStateWithRevert;
use Bga\Games\sanctuary\Managers\Players;
use Bga\Games\sanctuary\Managers\Tiles;
use Bga\Games\sanctuary\Managers\Meeples;
use Bga\Games\sanctuary\Models\Player;
use Bga\Games\sanctuary\States\EndScore;
use Bga\Games\Sanctuary\Managers\Globals;

class Administration extends ActionStateWithRevert
{
    function __construct(
        protected Game $game,
        protected ?AbstractNode $node = null
    ) {
        parent::__construct(
            $game,
            node: $node,
            id: States::ST_ADMINISTRATION,
            type: StateType::ACTIVE_PLAYER,
            description: clienttranslate('Administration: ${actplayer} must discard tiles ${discardCount} tiles'),
            descriptionMyTurn: clienttranslate('Administration: ${you} must discard tiles ${discardCount} tiles'),
        );
    }

    public function getActionArgs(int $activePlayerId): array
    {
        $hand = Players::get($activePlayerId)->getHand();
        return [
            'cardIds' => $hand->getIds(),
            'discardCount' => max(0, $hand->count() - 6),
        ];
    }

    public function isOptional(): bool
    {
        return false;
    }

    public function onEnteringState(int $activePlayerId)
    {
        if ($this->getActionArgs($activePlayerId)['discardCount'] === 0) {
            return $this->finishAdministration();
        }
    }

    #[PossibleAction]
    public function actDiscard(#[JsonParam(associative: null)] array $cardIds)
    {
        $player = Players::getActive();
        $args = $this->getActionArgs($player->getId());
        if (count($cardIds) !== $args['discardCount'] || count(array_unique($cardIds)) !== count($cardIds)) {
            throw new UserException(clienttranslate('You must discard the required number of tiles'));
        }
        foreach ($cardIds as $cardId) {
            if (!in_array($cardId, $args['cardIds'], true)) {
                throw new UserException(clienttranslate('This tile cannot be discarded'));
            }
        }

        [$discarded] = Tiles::discard($cardIds);
        $this->notify->all('discardTiles', clienttranslate('${player_name} discards ${card_names}'), [
            'player' => $player,
            'cards' => $discarded,
        ]);
        return $this->finishAdministration();
    }

    private function finishAdministration()
    {
        $endOfGame = false;
        $alreadyEnded = Globals::isEndTriggered();

        $player = Players::getActive();
        if (
            count($player->getSupportedAchievements()) >= 4
            || count($player->map()->getTiles()) >= count($player->map()->getListOfCells())
        ) {
            // Distribute marker score
            if (!$alreadyEnded) {
                Globals::setEndTriggered(true);
                $meeple = Meeples::singleCreate([
                    'type' => Meeples::END_GAME_FIRST,
                    'player_id' => $player->getId(),
                    'location' => 'reserve',
                    'state' => 0,
                ]);
                $this->notify->all('endTriggered', clienttranslate('${player_name} triggers the end of the game and receives the first end game marker'), [
                    'player' => $player,
                    'player_name' => $player->getName(),
                    'meeple' => $meeple,
                ]);
                Globals::setEndRemainingPlayers(Players::getAll()->getIds());
            } else {
                $meeple = Meeples::singleCreate([
                    'type' => Meeples::END_GAME_OTHERS,
                    'player_id' => $player->getId(),
                    'location' => 'reserve',
                    'state' => 0,
                ]);
                $this->notify->all('endTriggered', clienttranslate('${player_name} also triggers the end of the game and receives an end game marker'), [
                    'player' => $player,
                    'player_name' => $player->getName(),
                    'meeple' => $meeple,
                ]);
            }
            // end of game
            $endOfGame = true;
        }

        // Deck of tiles is empty and end of game has not been triggered yet
        if (Tiles::countInLocation('deck') === 0 && !Globals::isEndTriggered()) {
            return EndScore::class;
        } elseif (Tiles::countInLocation('deck') === 0) {
            // If end is already triggered, we ran reform the deck
            Tiles::moveAllInLocation('discard', 'deck');
            Tiles::shuffle('deck');
            $this->notify->all('deckReformed', clienttranslate('The deck of tiles has been reformed'), ['deckCount' => Tiles::countInLocation('deck')]);
        }
        Tiles::fillPool();

        if (Globals::isEndTriggered()) {
            Globals::setEndRemainingPlayers(array_diff(Globals::getEndRemainingPlayers(), [$player->getId()]));
            if (empty(Globals::getEndRemainingPlayers())) {
                return EndScore::class;
            }
        }

        return $this->resolve(['administration' => 'done']);
    }
}
