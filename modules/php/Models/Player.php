<?php

namespace Bga\Games\sanctuary\Models;

use Bga\Games\sanctuary\Game;
use Bga\Games\sanctuary\Managers\Tiles;
use Bga\Games\sanctuary\Managers\ActionCards;
use Bga\Games\sanctuary\Managers\Meeples;

use Bga\Games\Sanctuary\Managers\Globals;
use Bga\Games\sanctuary\Constants\Icons;

/**
 * Class representing a Player
 *
 */
class Player extends \Bga\Games\sanctuary\Framework\Models\Player
{
    protected ?string $table = 'player';
    protected ?string $primary = 'player_id';
    protected array $customAttributes = [
        "appeal" => ["appeal", "int"],
        "conservationMarker" => ["conservation_marker", "int"],

    ];

    protected array $staticAttributes = [];
    protected int $appeal;
    protected ?ZooMap $map = null;

    public function getUiData($currentPlayerId = null)
    {
        $data = parent::getUiData();
        $current = $this->id == $currentPlayerId;
        $data['hand'] = $current ? $this->getHand()->ui() : [];
        $data['handCount'] = $this->getHand()->count();
        $data['actionCards'] = $this->getActionCards()
            ->order(fn($first, $second) => $first->getStrength() <=> $second->getStrength())
            ->ui();
        $data['icons'] = $this->countCardIcons();
        $data['map'] = $this->map() ? $this->map()->getUiData() : null;
        return $data;
    }

    public function map(): ?ZooMap
    {
        if ($this->map == null) {
            $mapId = Globals::getMap();

            if (is_null($mapId) || $mapId == '') {
                return null;
            }

            $className = '\Bga\Games\sanctuary\Maps\Map' . $mapId;
            $this->map = new $className($this);
        }
        return $this->map;
    }

    /*
    ██╗  ██╗███████╗██╗     ██████╗ ███████╗██████╗ ███████╗
    ██║  ██║██╔════╝██║     ██╔══██╗██╔════╝██╔══██╗██╔════╝
    ███████║█████╗  ██║     ██████╔╝█████╗  ██████╔╝███████╗
    ██╔══██║██╔══╝  ██║     ██╔═══╝ ██╔══╝  ██╔══██╗╚════██║
    ██║  ██║███████╗███████╗██║     ███████╗██║  ██║███████║
    ╚═╝  ╚═╝╚══════╝╚══════╝╚═╝     ╚══════╝╚═╝  ╚═╝╚══════╝

    */

    public function canTakeAction($action, $ctx)
    {
        $stateClass = "\\Bga\\Games\\sanctuary\\States\\" . $this->action;
        return $stateClass(Game::get(), $ctx)->isDoable($this, $ctx);
    }

    public function isCardUpgraded($cardType)
    {
        $card = null;
        foreach (self::getActionCards() as $cId => $oCard) {
            if ($oCard->getActionType() == $cardType) {
                $card = $oCard;
            }
        }
        return is_null($card) ? false : $card->getLevel() == 2;
    }

    public function getTileRange()
    {
        $card = $this->getActionCardOfType('Project');
        if ($card->getLevel() == 2) {
            return $card->getStrength() + 1;
        } else {
            return $card->getStrength();
        }
    }

    /*
    ███████╗ ██████╗ ██████╗ ██████╗ ██╗███╗   ██╗ ██████╗
    ██╔════╝██╔════╝██╔═══██╗██╔══██╗██║████╗  ██║██╔════╝
    ███████╗██║     ██║   ██║██████╔╝██║██╔██╗ ██║██║  ███╗
    ╚════██║██║     ██║   ██║██╔══██╗██║██║╚██╗██║██║   ██║
    ███████║╚██████╗╚██████╔╝██║  ██║██║██║ ╚████║╚██████╔╝
    ╚══════╝ ╚═════╝ ╚═════╝ ╚═╝  ╚═╝╚═╝╚═╝  ╚═══╝ ╚═════╝

    */

    ////////////////////////////////////////////////////////////////
    //     _        _   _                ____              _
    //    / \   ___| |_(_) ___  _ __    / ___|__ _ _ __ __| |___
    //   / _ \ / __| __| |/ _ \| '_ \  | |   / _` | '__/ _` / __|
    //  / ___ \ (__| |_| | (_) | | | | | |__| (_| | | | (_| \__ \
    // /_/   \_\___|\__|_|\___/|_| |_|  \____\__,_|_|  \__,_|___/
    ////////////////////////////////////////////////////////////////
    public function getActionCards()
    {
        return ActionCards::getOfPlayer($this->id);
    }

    public function getActionCardInPosition($position)
    {
        return ActionCards::getInPosition($this->id, $position);
    }

    public function getActionCardOfType($type)
    {
        return $this->getActionCards()
            ->filter(function ($card) use ($type) {
                return $card->getActionType() == $type;
            })
            ->first();
    }

    public function getActionCardInUse()
    {
        foreach (self::getActionCards() as $cId => $card) {
            if ($card->getStatus() === 1) {
                return $card;
            }
        }
        return null;
    }

    public function moveActionCard($type, $position = 1)
    {
        $oCard = $this->getActionCardOfType($type);
        $initialPosition = $oCard->getStrength();
        // move all others cards on the right
        foreach ($this->getActionCards() as $cId => $card) {
            $loc = $card->getStrength();
            if ($position == 1 && $loc < $initialPosition) {
                $card->setStrength($loc + 1);
            } elseif ($position == 5 && $loc > $initialPosition) {
                $card->setStrength($loc - 1);
            }
        }
        $oCard->setStrength($position);
        return $this->getActionCards();
    }

    // conservation markers
    public function getAvailableAchievementMarkers()
    {
        return Meeples::getAvailableAchievementMarkers($this->id);
    }

    /**
     * Icons of the conservation projects already supported by this player
     */
    public function getSupportedAchievements(): array
    {
        return array_values(
            Meeples::getPlacedAchievementMarkers($this->id)
                ->map(fn($marker) => $marker->getLocationArg())
                ->toArray()
        );
    }

    /**
     * Achievement markers the player can still place on the conservation board.
     * Returns [markerId => ['type' => markerType, 'strength' => iconRequirement,
     * 'achievements' => [icon, ...],
     * 'conservationMarkers' => [icon => count, ...]]]
     */
    public function getPlayableAchievementMarkers(): array
    {
        $available = $this->getAvailableAchievementMarkers();
        if ($available->count() == 0) {
            return [];
        }

        $supported = $this->getSupportedAchievements();
        $board = Globals::getConservationBoard() ?? [];
        $icons = $this->countCardIcons();
        $conservationMarkers = max(0, $this->getConservationMarker());
        $result = [];
        foreach ($available as $marker) {
            $needed = Meeples::getAchievementRequirement($marker->getType());
            $achievements = [];
            $usedConservationMarkers = [];
            foreach ($board as $achievement) {
                if (in_array($achievement, $supported)) {
                    continue;
                }
                // At least 1 icon is required
                if ($icons[$achievement] == 0) {
                    continue;
                }
                $markersNeeded = max(0, $needed - ($icons[$achievement] ?? 0));
                if ($markersNeeded <= $conservationMarkers) {
                    $achievements[] = $achievement;
                    if ($markersNeeded > 0) {
                        $usedConservationMarkers[$achievement] = $markersNeeded;
                    }
                }
            }

            if (!empty($achievements)) {
                $result[$marker->getId()] = [
                    'type' => $marker->getType(),
                    'strength' => $needed,
                    'achievements' => $achievements,
                ];
                if (!empty($usedConservationMarkers)) {
                    $result[$marker->getId()]['conservationMarkers'] = $usedConservationMarkers;
                }
            }
        }
        return $result;
    }

    ///////////////////////////////////////////////////
    //  _____              ____              _
    // |__  /___   ___    / ___|__ _ _ __ __| |___
    //   / // _ \ / _ \  | |   / _` | '__/ _` / __|
    //  / /| (_) | (_) | | |__| (_| | | | (_| \__ \
    // /____\___/ \___/   \____\__,_|_|  \__,_|___/
    ///////////////////////////////////////////////////
    public function getHand($type = null)
    {
        return Tiles::getHand($this->id)->filter(function ($card) use ($type) {
            return is_null($type) || $card->getType() == $type;
        });
    }

    public function getStoredCards()
    {
        return Tiles::getFiltered($this->id, 'stored');
    }

    public function getHandLimit()
    {
        $baseLimit = 3;
        // if ($this->hasUniversity(\UNIVERSITY_REP_HAND)) {
        //     $baseLimit += 2;
        // }

        // if ($this->hasKeptBonusTile(BONUS_SNAP_CARDLIMIT)) {
        //     $baseLimit += 1;
        // }

        return $baseLimit;
    }

    public function getHandStatus(): array
    {
        $limit = $this->getHandLimit();
        $tooMuch = $this->getHand()->count() > $limit;
        return [$limit, $tooMuch];
    }

    public function getScoringHand()
    {
        return Tiles::getScoringHand($this->id);
    }

    public function getPlayedCards($type = null)
    {
        return Tiles::getPlayedCards($this->id, $type);
    }

    public function getNextRescueSlot()
    {
        $count = Tiles::getRescuedCards($this->id)->count();
        return $count == 3 ? null : $count;
    }

    public function getPlayedAnimal($icon = null)
    {
        $animals = $this->getPlayedCards(Tile::TILE_ANIMAL);
        if (!is_null($icon)) {
            $animals = $animals->filter(function ($animal) use ($icon) {
                return ($animal->getIcons()[$icon] ?? 0) > 0;
            });
        }
        return $animals;
    }

    // Useful for flocking
    public function getBiggestHerbivore()
    {
        $n = 0;
        foreach ($this->getPlayedAnimal(Icons::HERBIVORE) as $animal) {
            $n = max($n, $animal->getEnclosureSize());
        }
        return $n;
    }

    public function hasPlayedCard($id)
    {
        return Tiles::hasPlayedCard($this->id, $id);
    }


    public function getTilesInReputationRange($type = null)
    {
        $maxRange = $this->getTileRange();
        return Tiles::getPool($maxRange)->filter(function ($tile) use ($type) {
            return is_null($type) || $tile->getType() == $type;
        });
    }

    public function countCardIcon($icon)
    {
        $icons = $this->countCardIcons();
        return $icons[$icon] ?? 0;
    }

    public function countCardIcons($onlyNonZero = false, $toKeep = null)
    {
        $icons = [];

        foreach (Icons::CONTINENTS_AND_TYPES_AND_HABITATS as $type) {
            $icons[$type] = 0;
        }

        $cards = $this->getPlayedCards();
        foreach ($cards as $aId => $card) {
            foreach ($card->getIcons() as $type => $n) {
                $icons[$type] += $n;
            }
        }



        if (!is_null($toKeep)) {
            foreach (array_keys($icons) as $type) {
                if (!in_array($type, $toKeep)) {
                    unset($icons[$type]);
                }
            }
        }

        if ($onlyNonZero) {
            foreach (array_keys($icons) as $type) {
                if ($icons[$type] == 0) {
                    unset($icons[$type]);
                }
            }
        }

        // Update stats
        if (!$onlyNonZero && is_null($toKeep)) {
            foreach (Icons::CONTINENTS_AND_TYPES as $type) {
                if (!in_array($type, Icons::CONTINENTS_AND_TYPES) && !in_array($type, Icons::HABITATS)) {
                    continue;
                }

                $val = $icons[$type];
                $statName = 'getIcon' . $type;
                // if (Stats::$statName($this) != $val) {
                //     $statName = 'setIcon' . $type;
                //     Stats::$statName($this, $val);
                // }
            }
        }

        return $icons;
    }
}
