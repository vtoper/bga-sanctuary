import { Game } from '../Game';
import { players } from '../Players';
import { onClick } from '../framework/event';
import { clearPossible, performAction } from '../framework/utils';

export class Administration {
  game: Game;
  bga: ExtendedBga;

  private args: AdministrationArgs | null = null;
  private selectedCardIds: string[] = [];

  constructor(game: Game, bga: ExtendedBga) {
    this.game = game;
    this.bga = bga;
  }

  onEnteringState(args: AdministrationArgs, isCurrentPlayerActive: boolean) {
    this.args = args;
    this.selectedCardIds = [];
    if (isCurrentPlayerActive) {
      this.refresh();
    }
  }

  onLeavingState(args: object, isCurrentPlayerActive: boolean) {
    this.args = null;
    this.selectedCardIds = [];
    clearPossible();
  }

  private refresh() {
    clearPossible();
    this.makeHandSelectable();
    this.updateStatusBar();
  }

  private makeHandSelectable() {
    for (const cardId of players.getHandTileIds()) {
      const node = players.getHandTileNode(cardId);
      if (!node || !this.args?.cardIds.includes(cardId)) {
        continue;
      }

      node.classList.toggle('selected', this.selectedCardIds.includes(cardId));
      onClick(node, () => this.toggleCard(cardId));
    }
  }

  private toggleCard(cardId: string) {
    if (this.selectedCardIds.includes(cardId)) {
      this.selectedCardIds = this.selectedCardIds.filter((selectedId) => selectedId !== cardId);
    } else if (this.selectedCardIds.length < (this.args?.discardCount ?? 0)) {
      this.selectedCardIds.push(cardId);
    }
    this.refresh();
  }

  private updateStatusBar() {
    this.bga.statusBar.removeActionButtons();
    const required = this.args?.discardCount ?? 0;
    this.bga.statusBar.addActionButton(
      `${_('Discard')} (${this.selectedCardIds.length}/${required})`,
      () => performAction('actDiscard', { cardIds: JSON.stringify(this.selectedCardIds) }),
      { disabled: this.selectedCardIds.length !== required },
    );
  }
}
