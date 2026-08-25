<?php

declare(strict_types=1);

namespace Bga\Games\Sanctuary\States\Actions;

use Bga\Games\Sanctuary\Framework\Engine\AbstractNode;

use Bga\GameFramework\StateType;
use Bga\GameFramework\States\GameState;
use Bga\GameFramework\States\PossibleAction;
use Bga\GameFramework\UserException;
use Bga\Games\Sanctuary\Game;
use Bga\Games\Sanctuary\Constants\States;
use Bga\Games\Sanctuary\Framework\Engine\ActionStateWithRevert;

use Bga\Games\Sanctuary\Managers\Players;
use Bga\Games\Sanctuary\Managers\Tiles;
use Bga\Games\Sanctuary\Models\Player;
use Bga\Games\Sanctuary\Models\Tile;



class Project extends ActionStateWithRevert
{
    function __construct(
        protected Game $game,
        protected ?AbstractNode $node = null
    ) {
        parent::__construct(
            $game,
            node: $node,
            id: States::ST_PROJECT,
            type: StateType::ACTIVE_PLAYER,
            description: clienttranslate('${actplayer} must play a project tile with max level ${level}'),
            descriptionMyTurn: clienttranslate('${you} must play a project tile with max level ${level}'),
        );
    }

    public function getDescription()
    {
        if (!is_null($this->getSource())) {
            return [
                "log" => clienttranslate('Play a project tile (${source})'),
                "args" => [
                    "source" => $this->getSource() ?? ""
                ]
            ];
        }

        return clienttranslate('Play a project tile');
    }

    public function getActionArgs(int $activePlayerId): array
    {
        $player = Players::get($activePlayerId);
        $playable = $this->getPlayableTilesAndLocations($player);
        $args = [
            'source' => $this->getSource(),
            'level' => $this->getNodeArgs("strength", 1),
            '_private' => [
                $player->getId() => [
                    'playableTiles' => $playable,
                    'playableCardsIds' => array_keys($playable)
                ]
            ],
            '_merge_private' => true
        ];
        return $args;
    }

    /**
     * Compute, for each project tile in the player's hand that satisfies the strength constraints,
     * the list of locations on the ZooMap where it could be placed.
     *
     * @return array<string, array<array{x:int,y:int}>> map of tile id => list of locations
     */
    protected function getPlayableTilesAndLocations(Player $player): array
    {
        $maxStrength = $this->getNodeArgs("strength", 1);
        $map = $player->map();
        $locations = $map->getAvailableLocations();
        if (empty($locations)) {
            return [];
        }

        $result = [];
        foreach ($player->getHand(Tile::TILE_PROJECT) as $tileId => $project) {
            if ($project->matchesPlayConstraints($maxStrength)) {
                $newLocations = $locations;
                $result[$tileId] = $newLocations;

                if ($project->isRelease()) {
                    $possible = $map->getProjectReleaseOptions($project);
                    if (!empty($possible)) {
                        $result[$tileId] = $possible;
                    }
                }
            }
        }
        return $result;
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

    #[PossibleAction]
    public function actProject(string $tileId, string $location)
    {
        $player = Players::getCurrent();
        if ($player != Players::getActive()) {
            throw new UserException('Not your turn');
        }

        $args = $this->getArgs($player->getId());
        $playableTiles = $this->getPlayableTilesAndLocations($player);
        if (!isset($playableTiles[$tileId])) {
            throw new UserException('This project cannot be played. Should not happen');
        }
        $position = $this->parseLocation($location);
        if (!$this->containsCell($playableTiles[$tileId], $position)) {
            throw new UserException('This location is not available for this project. Should not happen');
        }

        $project = Tiles::get($tileId) ?? null;
        $map = $player->map();

        // if it's a release project, we need to replace existing tile
        // no placement bonus can occur
        if ($project->isRelease()) {
            $existingTile = $map->replaceTile($tileId, $position);
            $project = Tiles::get($project->getId()) ?? null; // reload the project tile after replacement

            $this->notify->all('projectReleased', clienttranslate('${player_name} plays ${project_name} and replace ${existing_tile_name}'), [
                'player' => $player,
                'player_name' => $player->getName(),
                'project' => $project,
                'project_name' => $project->getName(),
                'existing_tile_name' => $existingTile->getName(),
                'existingId' => $existingTile->getId(),
                'bonuses' => [],
                'i18n' => ['project_name', 'existing_tile_name'],
            ]);
        } else {
            [$playedProject, $bonuses] = $map->addTile($tileId, $position);

            $this->insertBonusesFlow($bonuses, clienttranslate('placement bonus'));
            $this->notify->all('buildingPlayed', clienttranslate('${player_name} plays ${project_name}'), [
                'player' => $player,
                'player_name' => $player->getName(),
                'project' => $playedProject,
                'project_name' => $playedProject->getName(),
                'bonuses' => $bonuses,
                'i18n' => ['project_name'],
            ]);
        }


        // TODO
        // Effects of the played tile to insert
        // Bonuses to insert
        // Reactions to insert
        //Tiles::applyEffects($player, 'AnimalPlayed', $effectArgs);
        // 

        return $this->resolve(['project', 'tileId' => $tileId]);
    }



    function zombie(int $playerId)
    {
        // // Example of zombie level 0: return NextPlayer::class; or $this->actPass($playerId);

        // // Example of zombie level 1:
        // $args = $this->getArgs();
        // $zombieChoice = $this->getRandomZombieChoice($args['playableCardsIds']); // random choice over possible moves
        // return $this->actBuilding($zombieChoice, $playerId, $args); // this function will return the transition to the next state
    }
}
