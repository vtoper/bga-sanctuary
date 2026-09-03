import { Game } from '../Game';
import { players } from '../Players';
import { onClick } from '../framework/event';
import { clearPossible, getCurrentPlayerId, performAction } from '../framework/utils';

export class MoveActionCard {
  game: Game;
  bga: ExtendedBga;
  private args: MoveActionCardArgs | null = null;
  private selectedCardId: number | null = null;

  constructor(game: Game, bga: ExtendedBga) {
    this.game = game;
    this.bga = bga;
  }

  onEnteringState(args: MoveActionCardArgs, isCurrentPlayerActive: boolean) {
    this.args = args;
    this.selectedCardId = null;
    if (isCurrentPlayerActive) {
      this.refresh();
    }
  }

  onLeavingState(args: object, isCurrentPlayerActive: boolean) {
    this.args = null;
    this.selectedCardId = null;
    clearPossible();
  }

  private refresh() {
    clearPossible();
    const playerId = getCurrentPlayerId();
    for (const card of this.args?.actionCards ?? []) {
      const node = document.getElementById(`action-card-${playerId}-${card.id}`);
      if (!node) {
        continue;
      }
      node.classList.toggle('selected', this.selectedCardId === card.id);
      onClick(node, () => this.selectCard(card.id));
    }
    console.debug(this.selectedCardId);
    this.bga.statusBar.removeActionButtons();
    this.bga.statusBar.addActionButton(_('Move to slot 1'), () => this.confirm(), {
      disabled: this.selectedCardId === null,
    });
  }

  private selectCard(cardId: number) {
    this.selectedCardId = this.selectedCardId === cardId ? null : cardId;
    this.refresh();
  }

  private confirm() {
    performAction('actMoveActionCard', { cardId: this.selectedCardId });
  }
}
