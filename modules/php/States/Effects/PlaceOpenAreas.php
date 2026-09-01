<?php

declare(strict_types=1);

namespace Bga\Games\Sanctuary\States\Effects;

use Bga\GameFramework\StateType;
use Bga\GameFramework\States\PossibleAction;
use Bga\GameFramework\UserException;
use Bga\Games\Sanctuary\Constants\States;
use Bga\Games\Sanctuary\Framework\Engine\AbstractNode;
use Bga\Games\Sanctuary\Framework\Engine\ActionStateWithRevert;
use Bga\Games\Sanctuary\Game;
use Bga\Games\Sanctuary\Managers\Players;
use Bga\Games\Sanctuary\Managers\Tiles;
use Bga\Games\Sanctuary\Models\Player;

class PlaceOpenAreas extends ActionStateWithRevert
{
    function __construct(
        protected Game $game,
        protected ?AbstractNode $node = null
    ) {
        parent::__construct(
            $game,
            node: $node,
            id: States::ST_PLACE_OPEN_AREAS,
            type: StateType::ACTIVE_PLAYER,
            description: clienttranslate('${actplayer} must place ${n}/${totalAreas} open area(s)'),
            descriptionMyTurn: clienttranslate('${you} must place ${n}/${totalAreas} open area(s)'),
        );
    }

    public function getActionArgs(int $activePlayerId): array
    {
        // var_dump($this->getNodeArgs());
        // var_dump($this->getNodeArgs());
        // throw new \feException(print_r($this->getNodeArgs()));
        return [
            'n' => $this->getOpenAreasRemaining(),
            'totalAreas' => $this->getNodeArgs('n', 1),
            '_private' => [
                $activePlayerId => [
                    'locations' => Players::get($activePlayerId)->map()->getAvailableLocations(),
                ],
            ],
            '_merge_private' => true,
        ];
    }

    public function isDoable($player): bool
    {
        $player = $player === null ? Players::getActive() : Players::get($player->getId());

        return Tiles::countInLocation(Tiles::DECK) > 0
            && !empty($player->map()->getAvailableLocations())
            && $this->getOpenAreasRemaining() > 0;
    }

    #[PossibleAction]
    public function actPlaceOpenArea(string $location)
    {
        $player = Players::getCurrent();
        if ($player != Players::getActive()) {
            throw new UserException(clienttranslate('Not your turn'));
        }

        return $this->placeOpenArea($player, $location);
    }

    private function placeOpenArea(Player $player, string $location)
    {
        if (Tiles::countInLocation(Tiles::DECK) === 0) {
            throw new UserException(clienttranslate('There are no tiles left in the deck'));
        }

        $position = $this->parseLocation($location);
        if (!$this->containsCell($player->map()->getAvailableLocations(), $position)) {
            throw new UserException(clienttranslate('This location is not available'));
        }

        $tile = Tiles::pickOneForLocation(Tiles::DECK, Tiles::HAND, deckReform: false);
        if ($tile === null) {
            throw new UserException(clienttranslate('There are no tiles left in the deck'));
        }
        $tile->setPId($player->getId());

        [$openArea, $bonuses] = $player->map()->addOpenArea($tile->getId(), $position);
        $this->insertBonusesFlow($bonuses, clienttranslate('placement bonus'));

        $this->notify->all('openAreaPlaced', clienttranslate('${player_name} places an open area'), [
            'player' => $player,
            'openArea' => $openArea,
            'bonuses' => $bonuses,
        ]);

        $placed = $this->getNodeArgs('placed', 0) + 1;
        if ($placed < $this->getNodeArgs('n', 1) && Tiles::countInLocation(Tiles::DECK) > 0) {
            $this->duplicateAction(['placed' => $placed]);
        }

        return $this->resolve(['n' => $placed]);
    }

    function zombie(int $playerId)
    {
        $locations = Players::get($playerId)->map()->getAvailableLocations();
        if (empty($locations) || Tiles::countInLocation(Tiles::DECK) === 0) {
            return $this->resolve(['n' => 0]);
        }

        $location = $locations[0];
        return $this->placeOpenArea(Players::get($playerId), $location['x'] . '_' . $location['y']);
    }

    private function getOpenAreasRemaining(): int
    {
        return $this->getNodeArgs('placed', 0) + 1;
    }

    private function parseLocation(string $location): array
    {
        $parts = explode('_', $location);
        if (count($parts) !== 2 || filter_var($parts[0], FILTER_VALIDATE_INT) === false || filter_var($parts[1], FILTER_VALIDATE_INT) === false) {
            throw new UserException(clienttranslate('Invalid location'));
        }

        return ['x' => (int) $parts[0], 'y' => (int) $parts[1]];
    }

    private function containsCell(array $locations, array $needle): bool
    {
        foreach ($locations as $location) {
            if ((int) $location['x'] === $needle['x'] && (int) $location['y'] === $needle['y']) {
                return true;
            }
        }

        return false;
    }
}
