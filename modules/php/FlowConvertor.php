<?php

namespace Bga\Games\Sanctuary;

use Bga\Games\Sanctuary\Models\ZooMap;
use Bga\Games\Sanctuary\Managers\Globals;
use Bga\Games\Sanctuary\Managers\Players;
use Bga\Games\Sanctuary\States\Actions\TakeBonus;
use Bga\Games\Sanctuary\Framework\Engine\Utils;
use Bga\Games\Sanctuary\Constants\Effects;

// effects
use Bga\Games\Sanctuary\States\Actions\Gain;
use Bga\Games\Sanctuary\States\Actions\DrawTile;
use Bga\Games\Sanctuary\States\Actions\TakeTile;
use Bga\Games\Sanctuary\States\Effects\Hunter;
use Bga\Games\Sanctuary\States\Effects\PlaceOpenAreas;
use Bga\Games\Sanctuary\States\Effects\Pouch;
use Bga\Games\Sanctuary\States\Effects\MoveActionCard;
use Bga\GameFramework\SystemException;

// Allow to use a short flow description syntax
abstract class FlowConvertor
{
  /**
   * getFlow: given an array of bonuses, return the list of corresponding actions
   *  - array of bonuses follow this format :
   *    [
   *      [BONUS_TYPE => BONUS_N],
   *      [BONUS_TYPE => BONUS_N]
   *    ]
   */
  public static function getFlow($bonuses, $source = '', $sourceType = null, $sourceId = null)
  {
    $immediateBonuses = [];
    $afterFinishingBonuses = [];
    foreach ($bonuses as $bonus) {
      // If bonus is already a node, no conversion needed
      if (isset($bonus['action']) || (isset($bonus['type']) && isset($bonus['childs']))) {
        if ($bonus['afterFinishing'] ?? false) {
          $afterFinishingBonuses[] = $bonus;
        } else {
          $immediateBonuses[] = $bonus;
        }
        continue;
      }

      $type = array_keys($bonus)[0];
      $n = $bonus[$type];
      $node = self::getFlowSingleBonus($type, $n, $bonus['source'] ?? $source, $bonus['sourceId'] ?? $sourceId, $bonus);
      if (is_null($node)) {
        continue;
      }

      // If the bonuses come from a bonusTile / incomeBonusSpace, wrap flow inside a TAKE_BONUS action for nicer UI
      if (
        ($sourceType == 'bonusTile' || $sourceType == 'incomeBonusSpace') &&
        is_null($bonus['sourceId'] ?? $sourceId) &&
        is_null($bonus['source'] ?? null)
      ) {
        $node = [
          'state' => TakeBonus::class,
          'args' => [
            'type' => $type,
            'n' => $bonus[$type],
            'sourceType' => $sourceType,
            'source' => $source,
            'sourceId' => $sourceId,
          ],
        ];
      }

      if (in_array($type, [Effects::MOVE_ACTION_CARD]) || ($bonus['afterFinishing'] ?? false)) {
        $afterFinishingBonuses[] = $node;
      } else {
        $immediateBonuses[] = $node;
      }
    }
    return [$immediateBonuses, $afterFinishingBonuses];
  }

  /**
   * getFlowSingleBonus: given a bonus with its type and n, return the corresponding action
   */
  public static function getFlowSingleBonus($type, $n, $source = '', $sourceId = null, $args = [])
  {
    $flow = self::getFlowSingleBonusAux($type, $n, $args);
    $data = [];
    if (is_null($flow)) {
      return null;
    }

    if (!is_null($sourceId) && !isset($data['sourceId'])) {
      $data['sourceId'] = $sourceId;
    } elseif (!is_null($source) && $source != '' && !isset($data['source'])) {
      $data['source'] = $source;
    }

    return Utils::tagTree($flow, $data);
  }

  public static function getFlowSingleBonusAux($type, $n, $args = [])
  {
    // Basic resources via GAIN action
    if (in_array($type, [Effects::CONSERVATION, Effects::APPEAL])) {
      // Normal gain
      $data = [
        // TODO
        'state' => Gain::class,
        'args' => [$type => $n],
      ];
      if (isset($args['pId'])) {
        $data['args']['pId'] = $args['pId'];
      }
      if ($args['income'] ?? false) {
        $data['args']['income'] = true;
      }
      if (isset($args['map'])) {
        $data['args']['map'] = $args['map'];
      }
      if (isset($args['source'])) {
        $data['source'] = $args['source'];
      }
      return $data;
    } elseif ($type == Effects::TAKE_ALL_TILES) {
      return [
        'state' => TakeTile::class,
        'args' => [
          'max' => 99,
          'inRange' => false,
          'typeFilter' => $n,
          'source' => $args['source'] ?? '',
        ],
      ];
    } elseif ($type == Effects::TAKE_TILE) {
      return [
        'state' => TakeTile::class,
        'args' => [
          'max' => 1,
          'inRange' => false,
          'typeFilter' => $n,
          'source' => $args['source'] ?? '',
        ],
      ];
    }
    // // Upgrade an action card
    // elseif ($type == BONUS_UPGRADE_CARD) {
    //   return ['action' => UPGRADE_CARD];
    // }
    // ACTIONS THAT NEEDS TO BE TAKEN X-TIMES
    // TODO Sanctuary check if it will be needed
    // elseif (in_array($type, [CLEVER, INCREASE_SIZE]) && ($n ?? 1) > 1) {
    //   $nodes = [];
    //   for ($i = 0; $i < $n; $i++) {
    //     $nodes[] = ['action' => $type];
    //   }
    //   return ['type' => NODE_SEQ, 'childs' => $nodes];
    // }
    // Default behavior : action name = bonus name
    else {
      $effectClass = self::effectClassMapping[$type] ?? null;
      if (is_null($effectClass)) {
        throw new SystemException("Missing mapping for " . $type . " in FlowConvertor::effectClassMapping. Should not happen");
      }
      $node = ['state' => $effectClass, 'args' => ['n' => $n]];
      if ($args['optional'] ?? false) {
        $node['optional'] = true;
      }
      return $node;
    }

    die('TakeBonus : bonus type flow not found for ' . $type);
  }

  /**
   * Return the conservation bonuses rewarded at a given conservation
   */
  public static function getConservationBonusesXORNode($conservation)
  {
    // Any bonus here ?
    $bonusMap = Globals::getBonusTiles();
    $slot = $bonusMap[$conservation] ?? null;
    if (is_null($slot)) {
      return null;
    }

    // Compute node for each of them
    $childs = [];
    foreach ($slot as $i => $data) {
      $bonus = $data['bonus'];
      $type = array_keys($bonus)[0];
      $childs[] = [
        'action' => TAKE_BONUS,
        'args' => [
          'type' => $type,
          'n' => $bonus[$type],
          'remove' => $data['permanent'] ? '' : "$conservation-$i",
          'income' => false,
        ],
      ];
    }

    return empty($childs)
      ? null
      : (count($childs) == 1
        ? $childs[0]
        : [
          'type' => \NODE_XOR,
          'childs' => $childs,
        ]);
  }

  const effectClassMapping = [
    Effects::CONSERVATION => Gain::class,
    Effects::APPEAL => Gain::class,
    Effects::MOVE_ACTION_CARD => MoveActionCard::class,
    Effects::DRAW_TILE => DrawTile::class,
    Effects::TAKE_TILE => TakeTile::class,
    Effects::HUNTER => Hunter::class,
    Effects::PLACE_OPEN_AREAS => PlaceOpenAreas::class,
    Effects::POUCH => Pouch::class,
  ];
}
