<?php

namespace Bga\Games\Sanctuary\States\Actions;

use Bga\Games\Sanctuary\Managers\Meeples;
use Bga\Games\Sanctuary\Constants\Effects;
use Bga\Games\Sanctuary\Managers\Players;
use Bga\Games\Sanctuary\Game;
use Bga\Games\Sanctuary\Constants\States;
use Bga\GameFramework\StateType;


use Bga\Games\Sanctuary\Managers\Tiles;
use Bga\Games\Sanctuary\Models\Player;
use Bga\Games\Sanctuary\Framework\Models\Player as AbstractPlayer;
use Bga\Games\Sanctuary\Managers\Globals;
use Bga\Games\Sanctuary\Framework\Engine\AbstractNode;
use Bga\Games\Sanctuary\Framework\Engine\AutomaticActionState;

use Bga\Games\Sanctuary\Framework\Engine\Utils;

class Gain extends AutomaticActionState
{
  function __construct(
    protected Game $game,
    protected ?AbstractNode $node = null
  ) {
    parent::__construct(
      $game,
      node: $node,
      id: States::ST_GAIN,
      type: StateType::GAME,
    );
  }

  public function getDescription(): string|array
  {
    $player = $this->getPlayer();
    $gain = $this->getGain();
    $desc = Utils::resourcesToStr([$gain[0] => $gain[1]], true);

    if ($player->getId() == Players::getActiveId()) {
      return [
        'log' => clienttranslate('Gain ${resources_desc}'),
        'args' => [
          'resources_desc' => $desc,
        ],
      ];
    }
    // The reward is for someone else
    else {
      return [
        'log' => clienttranslate('Let ${player_name} gain ${resources_desc}'),
        'args' => [
          'player_name' => $player->getName(),
          'resources_desc' => $desc,
        ],
      ];
    }
  }

  public function isAutomatic(?AbstractPlayer $player = null): bool
  {
    return true;
  }

  public function isIndependent(?AbstractPlayer $player = null): bool
  {
    return true;
  }

  public function getPlayer(): Player
  {
    $args = $this->getNodeArgs();
    $pId = $args['pId'] ?? Players::getActiveId();
    return Players::get($pId);
  }

  public function getGain()
  {
    $args = $this->getNodeArgs();
    foreach ($args as $resource => $amount) {
      if (in_array($resource, ['cardId', 'pId', 'sourceId', 'source', 'income', 'map'])) {
        continue;
      }

      if (!in_array($resource, [Effects::CONSERVATION])) {
        die('GAIN: unrecognized resource' . $resource);
      }

      // MAP
      if (isset($args['map'])) {
        $amount = $args['map'][$amount] ?? max($args['map']);
      }

      return [$resource, $amount];
    }
    die('GAIN: resource not found');
  }

  public function onEnteringState(int $activePlayerId)
  {
    $player = $this->getPlayer();
    $args = $this->getNodeArgs();
    list($resource, $amount) = $this->getGain();

    $sourceId = $this->getSourceId() ?? null;
    $source = $this->getSource() ?? null;
    if (is_null($sourceId)) {
      $source = null;
    } else {
      $source = Tiles::getSingle($sourceId);
    }

    // Increase resource and notify
    // Get the previous amount
    $getMethod = 'get' . ucfirst($resource);
    $previousAmount = $player->$getMethod();

    $method = 'inc' . ucfirst($resource);
    $bonuses = $player->$method($amount, false);

    // Get the new amount and update the real bonus
    $newAmount = $player->$getMethod();
    $gains = [];
    $gains[$resource] = $newAmount - $previousAmount;

    // Notify
    $msg = clienttranslate('${player_name} gains ${bonuses} (${card_name})');
    $args['card_id'] = $source->getId();
    $args['card_name'] = $source->getName();
    $args['i18n'][] = 'card_name';
    $args['preserve'][] = 'card_id';
    $args['player'] = $player;
    // $args['score'] = $player->updateScore();
    $args['bonuses'] = $bonuses;
    /// TODO: update to manage accordingly notif
    $this->notify->all('getBonuses', $msg, $args);


    return $this->resolve(['gain' => $gains]);
  }
}
