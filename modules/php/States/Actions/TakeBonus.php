<?php

namespace Bga\Games\Sanctuary\States\Actions;

use Bga\Games\Sanctuary\Managers\Meeples;
use Bga\Games\Sanctuary\Models\ZooMap;

use Bga\Games\Sanctuary\Helpers\Utils;
// use Bga\Games\Sanctuary\Models\Player;
use Bga\Games\sanctuary\Framework\Models\Player;

use Bga\Games\Sanctuary\Managers\Players;

use Bga\GameFramework\StateType;
use Bga\Games\Sanctuary\Game;
use Bga\Games\Sanctuary\Constants\States;
use Bga\Games\Sanctuary\Framework\Db\Log;
use Bga\Games\Sanctuary\Framework\Engine\AbstractNode;
use Bga\Games\Sanctuary\Framework\Engine\AutomaticActionState;
use Bga\Games\Sanctuary\FlowConvertor;
use Bga\Games\Sanctuary\Framework\Engine\Engine;
use Bga\Games\Sanctuary\Constants\Effects;


class TakeBonus extends AutomaticActionState
{
  function __construct(
    protected Game $game,
    protected ?AbstractNode $node = null
  ) {
    parent::__construct(
      $game,
      node: $node,
      id: States::ST_TAKE_BONUS,
      type: StateType::GAME,
    );
  }


  public function getBonus()
  {
    return [$this->getNodeArgs('type', ''), $this->getNodeArgs('n', 1), $this->getNodeArgs('source', null)];
  }

  public function isIrreversible(?Player $player = null): bool
  {
    list($type, $n, $source) = $this->getBonus();
    return $type == ZooMap::DRAW_TILE;
  }

  public function getFlow($player)
  {
    list($type, $n, $source) = $this->getBonus();

    return FlowConvertor::getFlowSingleBonus($type, $n, $source);
  }

  public function getFlowTree($player)
  {
    list($type, $n) = $this->getBonus();
    $flow = $this->getFlow($player);
    return is_null($flow) ? null : Engine::buildTree($flow);
  }

  public function isOptional(): bool
  {
    $player = $this->getPlayer();
    if (is_null($this->getFlowTree($player))) {
      return true;
    }
    return $this->getFlowTree($player)->isOptional();
  }

  public function isAutomatic(?Player $player = null): bool
  {
    return true;
  }

  public function isDoable(int|Player $playerId): bool
  {
    $flowTree = $this->getFlowTree($playerId);
    return is_null($flowTree) ? false : $flowTree->isDoable($playerId);
  }

  public function doNotDisplayIfNotDoable(): bool
  {
    $player = Players::getActive();
    $flowTree = $this->getFlowTree($player);
    return is_null($flowTree) ? false : $flowTree->doNotDisplayIfNotDoable();
  }

  public function isIndependent(?Player $player = null): bool
  {
    $flowTree = $this->getFlowTree($player);
    return is_null($flowTree) ? false : $flowTree->isIndependent($player);
  }

  public function getDescription(): string|array
  {
    $flowTree = $this->getFlowTree($this->getPlayer());
    if (is_null($flowTree)) {
      return '';
    }

    $flowDesc = $flowTree->getDescription();
    list($type, $n) = $this->getBonus();
    if ($this->getNodeArgs('noIcon', false)) {
      return $flowDesc; // No icon for discard scoring cards
    }

    return [
      'log' => '${bonus_pentagon} : ${flowDesc}',
      'args' => [
        'i18n' => ['flowDesc'],
        'flowDesc' => $flowDesc,
        'bonus_source_type' => $this->getNodeArgs('sourceType', 'bonus'),
        'bonus_pentagon' => '',
        'bonus_type' => $type,
        'bonus_n' => $n,
      ],
    ];
  }

  public function stTakeBonus()
  {
    $player = $this->getPlayer();
    $args = $this->getNodeArgs();
    list($type, $n, $source) = $this->getBonus();
    $sourceType = $this->getNodeArgs('sourceType', 'bonus');

    // Replace this node by the actual flow of the bonus
    $node = $this->node;
    $flow = $this->getFlow($player);
    if ($node->isMandatory()) {
      $flow['optional'] = false; // Remove optional to avoid double confirmation UX
    }

    if (in_array($type, [Effects::MOVE_ACTION_CARD])) {
      Engine::pushAfterFinishingChilds([$flow]);
      $this->resolve(['afterFinishing']);
    } else {
      $node->replace(Engine::buildTree($flow));
      if ($type == Effects::DRAW_TILE) {
        Engine::checkpoint();
      }

      Engine::save();
      Engine::proceed();
    }
  }
}
