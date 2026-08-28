<?php

declare(strict_types=1);

namespace Bga\Games\Sanctuary\States\Actions;

use Bga\GameFramework\Actions\Types\JsonParam;
use Bga\GameFramework\StateType;
use Bga\GameFramework\States\GameState;
use Bga\GameFramework\States\PossibleAction;
use Bga\GameFramework\UserException;
use Bga\GameFramework\SystemException;
use Bga\Games\Sanctuary\Game;
use Bga\Games\Sanctuary\Constants\States;
use Bga\Games\Sanctuary\Framework\Db\Log;
use Bga\Games\Sanctuary\Framework\Engine\AbstractNode;
use Bga\Games\Sanctuary\Framework\Engine\ActionStateWithRevert;
use Bga\Games\sanctuary\Framework\Engine\Engine;
use Bga\Games\sanctuary\Framework\Models\Player as ModelsPlayer;
use Bga\Games\Sanctuary\Managers\Players;
use Bga\Games\Sanctuary\Managers\Meeples;
use Bga\Games\Sanctuary\Managers\ActionCards;
use Bga\Games\sanctuary\Constants\Icons;
use Bga\Games\sanctuary\Models\ActionCard;
use Bga\Games\Sanctuary\Models\Player;
use Bga\Games\Sanctuary\Models\Tile;
use Override;

class Upgrade extends ActionStateWithRevert
{
    function __construct(
        protected Game $game,
        protected ?AbstractNode $node = null
    ) {
        parent::__construct(
            $game,
            node: $node,
            id: States::ST_UPGRADE,
            type: StateType::ACTIVE_PLAYER,
            description: clienttranslate('${actplayer} may upgrade an action card'),
            descriptionMyTurn: clienttranslate('${you} may upgrade an action card'),
        );
    }

    public function getDescription()
    {
        if (!is_null($this->getSource())) {
            return [
                "log" => clienttranslate('Upgrade an action card (${source})'),
                "args" => [
                    "source" => $this->getSource() ?? ""
                ]
            ];
        }

        return clienttranslate('Upgrade an action card');
    }


    public function getActionArgs(int $activePlayerId): array
    {
        $player = Players::get($activePlayerId);
        $args = [
            'source' => $this->getSource(),
            'playableUpgrades' => $this->getPlayableUpgrades($player),
            'actionCards' => $player->getActionCards()->toArray()
        ];
        return $args;
    }

    public function isDoable(int|ModelsPlayer $playerId): bool
    {
        $player = $playerId instanceof Player ? $playerId : Players::get($playerId);
        return count($this->getPlayableUpgrades($player)) > 0;
    }

    public function onEnteringState(int $activePlayerId)
    {
        if (!$this->isDoable($activePlayerId)) {
            return $this->resolve(['pass']);
        }
    }

    #[PossibleAction]
    public function actUpgrade(int $tokenId, int $cardId)
    {
        $player = Players::getCurrent();
        if ($player != Players::getActive()) {
            throw new UserException('Not your turn');
        }

        $args = $this->getActionArgs($player->getId());
        $playableUpgrades = $args['playableUpgrades'] ?? [];
        $upgrade = $playableUpgrades[$tokenId] ?? null;
        $actionCards = $player->getActionCards()->getIds();
        if ($upgrade === null || !in_array($cardId, $actionCards)) {
            throw new SystemException('This upgrade is not available. Should not happen');
        }

        $token = Meeples::get($tokenId);
        if ($token === null || $token->getPId() != $player->getId() || $token->getLocation() != 'reserve') {
            throw new SystemException('This upgrade token is not available. Should not happen');
        }

        $card = ActionCards::get($cardId);
        if ($card === null || $card->getPId() != $player->getId() || $card->getLevel() != 1) {
            throw new SystemException('This action card cannot be upgraded. Should not happen');
        }

        $card->setLevel(2);
        Meeples::move($tokenId, 'used');

        $this->notify->all('actionCardUpgraded', clienttranslate('${player_name} upgrades an action card and use upgrade token ${token_type}'), [
            'player' => $player,
            'player_name' => $player->getName(),
            'token' => $token,
            'token_id' => $token->getId(),
            'token_type' => $token->getType(),
            'card' => $card,
            'card_id' => $card->getId(),
            'card_type' => $card->getActionType(),
        ]);

        if (!empty($this->getPlayableUpgrades($player))) {
            Engine::pushAfterFinishingChilds([
                [
                    'state' => self::class,
                    'args' => [],
                ],
            ]);
        }
        if (count($this->getPlayableUpgrades($player)) > 0) {
            return self::class;
        }

        return $this->resolve(['cardId' => $cardId]);
    }

    /**
     * Returns [tokenId => ['type' => tokenType, 'actionCards' => [cardId, ...]]].
     */
    private function getPlayableUpgrades(Player $player): array
    {
        $conditions = [
            Meeples::UPGRADE_CONSERVATION => count($player->getSupportedAchievements()) > 0,
            Meeples::UPGRADE_2PROJECTS => $this->countProjects($player) >= 2,
            Meeples::UPGRADE_3CONNECTED => $this->hasConnectedHabitat($player),
            Meeples::UPGRADE_4ANIMALS => $this->hasFourAnimalTypes($player),
        ];

        $result = [];
        foreach (Meeples::getAvailableUpgradeMarkers($player->getId()) as $token) {
            if ($conditions[$token->getType()] ?? false) {
                $result[$token->getId()] = [
                    'type' => $token->getType(),
                ];
            }
        }
        return $result;
    }

    private function countProjects(Player $player): int
    {
        return $player->map()->getTiles()->filter(fn(Tile $tile) => $tile->getType() === Tile::TILE_PROJECT)->count();
    }

    private function hasConnectedHabitat(Player $player): bool
    {
        $map = $player->map();
        $tilesByPosition = [];
        foreach ($map->getTiles() as $tile) {
            foreach (Icons::HABITATS as $habitat) {
                if (($tile->getIcons()[$habitat] ?? 0) > 0) {
                    $tilesByPosition[$tile->getX() . '_' . $tile->getY()] = $tile;
                    break;
                }
            }
        }

        foreach (Icons::HABITATS as $habitat) {
            $habitatTiles = array_filter($tilesByPosition, fn(Tile $tile) => ($tile->getIcons()[$habitat] ?? 0) > 0);
            foreach ($habitatTiles as $tile) {
                $visited = [];
                $queue = [$tile];
                while (!empty($queue)) {
                    $current = array_shift($queue);
                    $position = $current->getX() . '_' . $current->getY();
                    if (isset($visited[$position])) {
                        continue;
                    }
                    $visited[$position] = true;
                    foreach ($map->getNeighbours(['x' => $current->getX(), 'y' => $current->getY()]) as $cell) {
                        $neighbour = $habitatTiles[$cell['x'] . '_' . $cell['y']] ?? null;
                        if ($neighbour !== null) {
                            $queue[] = $neighbour;
                        }
                    }
                }
                if (count($visited) >= 3) {
                    return true;
                }
            }
        }
        return false;
    }

    private function hasFourAnimalTypes(Player $player): bool
    {
        $animalIcons = $player->countCardIcons(true, Icons::ANIMAL_TYPES);
        return count($animalIcons) >= 4;
    }


    /**
     * This method is called each time it is the turn of a player who has quit the game (= "zombie" player).
     * You can do whatever you want in order to make sure the turn of this player ends appropriately
     * (ex: play a random card).
     * 
     * See more about Zombie Mode: https://en.doc.boardgamearena.com/Zombie_Mode
     *
     * Important: your zombie code will be called when the player leaves the game. This action is triggered
     * from the main site and propagated to the gameserver from a server, not from a browser.
     * As a consequence, there is no current player associated to this action. In your zombieTurn function,
     * you must _never_ use `getCurrentPlayerId()` or `getCurrentPlayerName()`, 
     * but use the $playerId passed in parameter and $this->game->getPlayerNameById($playerId) instead.
     */
    function zombie(int $playerId)
    {
        // Example of zombie level 0: return NextPlayer::class; or $this->actPass($playerId);

        // Example of zombie level 1:
        $args = $this->getArgs($playerId);
        $tileId = $this->getRandomZombieChoice($args['playableCardsIds']);
        // TODO
    }
}
