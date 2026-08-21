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
use Bga\Games\Sanctuary\Framework\Engine\ActionStateWithRevert;
use Bga\Games\Sanctuary\Managers\Players;
use Bga\Games\Sanctuary\Models\Player;
use Bga\Games\Sanctuary\Models\Tile;
use Bga\Games\Sanctuary\Models\ZooMap;

class PlayAnimal extends ActionStateWithRevert
{
    function __construct(
        protected Game $game,
        protected ?AbstractNode $node = null
    ) {
        parent::__construct(
            $game,
            node: $node,
            id: States::ST_PLAY_CARD,
            type: StateType::ACTIVE_PLAYER,
            description: clienttranslate('${actplayer} must play a ${habitat} or undefined animal with max level ${level}'),
            descriptionMyTurn: clienttranslate('${you} must play a ${habitat} or undefined animal with max level ${level}'),
        );
    }

    public function getCustomStateDescription()
    {
        if (!is_null($this->getNodeArgs("sourceName"))) {
            return [
                "description" => clienttranslate('${actplayer} must play a ${habitat} or undefined animal with max level ${level} (${sourceName})'),
                "descriptionMyTurn" => clienttranslate('${you} must play a ${habitat} or undefined animal with max level ${level} (${sourceName})'),
            ];
        }
        return null;
    }

    public function getDescription()
    {
        if (!is_null($this->getNodeArgs("sourceName"))) {
            return [
                "log" => clienttranslate('Play card (${sourceName})'),
                "args" => [
                    "sourceName" => $this->getNodeArgs("sourceName", "")
                ]
            ];
        }
        if (!is_null($this->getNodeArgs("habitat"))) {
            return [
                "log" => clienttranslate('Play ${habitat}'),
                "args" => [
                    "habitat" => $this->getNodeArgs("habitat", "")
                ]
            ];
        }
        return clienttranslate('Play card');
    }


    public function getActionArgs(int $activePlayerId): array
    {
        $player = Players::get($activePlayerId);
        $playable = $this->getPlayableTilesAndLocations($player);
        $args = [
            'habitat' => $this->getNodeArgs("habitat", ""),
            'level' => $this->getNodeArgs("strength", 1),
            'sourceName' => "truc",
            'playableTiles' => $playable[0],
            'existingOpenAreas' => $playable[1]

        ];
        $args['playableCardsIds'] = array_keys($args['playableTiles']);
        return $args;
    }

    /**
     * Compute, for each animal tile in the player's hand that satisfies the strength/habitat constraints,
     * the list of locations on the ZooMap where it could be placed.
     *
     * @return array<string, array<array{x:int,y:int}>> map of tile id => list of locations
     */
    protected function getPlayableTilesAndLocations(Player $player): array
    {
        $maxStrength = $this->getNodeArgs("strength", 1);
        $habitat = $this->getNodeArgs("habitat", null);
        $map = $player->map();
        $locations = $map->getAvailableLocations();
        if (empty($locations)) {
            return [];
        }

        $result = [];
        $openAreas  = [];
        $handCount = $player->getHand()->count();
        foreach ($player->getHand(Tile::TILE_ANIMAL) as $tileId => $animal) {
            if ($animal->matchesPlayConstraints($maxStrength, $habitat)) {
                $newLocations = $locations;
                $existingOpenAreas = [];
                if ($animal->getOpenAreas() !== []) {
                    $mandatoryOpenAreas = $animal->getOpenAreas();
                    list($newLocations, $existingOpenAreas) = $map->checkMandatoryOpenAreas($mandatoryOpenAreas, $locations);
                    if (count($animal->getOpenAreas())  < count($existingOpenAreas)  && $handCount < (count($animal->getOpenAreas())  - count($existingOpenAreas))) {
                        // card cannot be placed as not enough card in hands to place the open areas    
                        $result[$tileId] = [];
                        continue;
                    }
                    $openAreas[$tileId] = $existingOpenAreas;
                }

                $result[$tileId] = $newLocations;
            }
        }
        return [$result, $openAreas];
    }

    /**
     * Player action, example content.
     *
     * In this scenario, each time a player plays a card, this method will be called. This method is called directly
     * by the action trigger on the front side with `bgaPerformAction`.
     *
     * @throws UserException
     */
    #[PossibleAction]
    public function actPlayAnimal(int $card_id, int $activePlayerId, array $args)
    {
        // check input values
        $playableCardsIds = $args['playableCardsIds'];
        if (!in_array($card_id, $playableCardsIds)) {
            throw new UserException('Invalid card choice');
        }

        // Add your game logic to play a card here.
        $card_name = Game::$CARD_TYPES[$card_id]['card_name'];

        // Notify all players about the card played.
        $this->bga->notify->all("cardPlayed", clienttranslate('${player_name} plays ${card_name}'), [
            "player_id" => $activePlayerId,
            "player_name" => $this->game->getPlayerNameById($activePlayerId), // remove this line if you uncomment notification decorator
            "card_name" => $card_name, // remove this line if you uncomment notification decorator
            "card_id" => $card_id,
            "i18n" => ['card_name'], // remove this line if you uncomment notification decorator
        ]);

        // in this example, the player gains 1 points each time he plays a card
        $this->bga->playerScore->inc($activePlayerId, 1);

        // at the end of the action, move to the next state
        return NextPlayer::class;
    }

    /**
     * Player action, example content.
     *
     * In this scenario, each time a player pass, this method will be called. This method is called directly
     * by the action trigger on the front side with `bgaPerformAction`.
     */
    #[PossibleAction]
    public function actPass(int $activePlayerId)
    {
        // Notify all players about the choice to pass.
        $this->notify->all("pass", clienttranslate('${player_name} passes'), [
            "player_id" => $activePlayerId,
            "player_name" => $this->game->getPlayerNameById($activePlayerId), // remove this line if you uncomment notification decorator
        ]);

        // in this example, the player gains 1 energy each time he passes
        $this->game->playerEnergy->inc($activePlayerId, 1);

        // at the end of the action, move to the next state
        return NextPlayer::class;
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
        $args = $this->getArgs();
        $zombieChoice = $this->getRandomZombieChoice($args['playableCardsIds']); // random choice over possible moves
        return $this->actPlayAnimal($zombieChoice, $playerId, $args); // this function will return the transition to the next state
    }
}
